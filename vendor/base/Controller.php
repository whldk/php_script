<?php
namespace vendor\base;

use vendor\base\Response;
use vendor\base\Validators;

use vendor\base\AppTrait;
use vendor\base\ErrorTrait;
use vendor\exceptions\UserErrorException;
use vendor\exceptions\AuthException;
use vendor\exceptions\InvalidConfigException;

/**
 * @property \vendor\base\User $user
 * @property \vendor\base\Response $response
 * @property \vendor\base\Request $request
 */
class Controller
{
	use AppTrait, ErrorTrait;
	
	const PAGESIZE = 10;
	const PAGE_PARAM = 'page';
	const PAGESIEZE_PARAM = 'pagesize';
	
	const MAX_PAGESIZE = 1000;
	
	protected $page = 0;
	protected $pagesize = null;
	
	public $actionMethod;	//action全名
	public $action;			//lowercase开头的action名，不含有action部分，驼峰模式
	protected $params;
	
	protected $behavior = null;
	
	const ERR_EMPTY = 1;
	const ERR_VALID = 2;
	const ERR_EXIST = 6;
	
	/**
	 * @var array
	 */
	protected $access = [];
	protected $filter = [];
	
	public function __construct($actionMethod)
	{
		$this->actionMethod = $actionMethod;
		$this->action = lcfirst(substr($actionMethod, 6));
	}
	
	public function run()
	{
		//before run
		$continue = $this->beforeRun();
		if ($continue !== true) {
			throw new UserErrorException($this->errors);
		}
		
		//execute
		$action = $this->actionMethod;
		
		return $this->$action();
	}
	
    public function params()
    {
    	return $this->params;
    }
	
	/**
	 * @throws AuthException
	 * @throws UserErrorException
	 * @return boolean
	 */
	protected function beforeRun()
	{
        //access
        if (!$this->access()) {
            throw new AuthException();
        }
        //params
        $this->params = $this->request->getBodyParams() ?: [];
        $this->params += $_GET;
        $this->params += $_FILES;

		//filter
		if (($valid = $this->filter()) !== true) {
			throw new UserErrorException($valid);
		}
		
		return true;
	}
	
	public function filter()
	{
	    //如果全是 *  则不验证任何参数
        $access = isset($this->access[$this->action]) ? $this->access[$this->action] : (isset($this->access['*']) ? $this->access['*'] : null);
        if (!$access) {
            return true;
        }
        if (in_array('*', $access, true)) {
            $role = '*';
        } else {
            $role = $this->user->getRole();
        }

		$filter = isset($this->filter[$this->action]) ? $this->filter[$this->action] : (isset($this->filter['*']) ? $this->filter['*'] : null);
		if (!$filter) {
			return true;
		}
		$filter = (isset($filter[$role]) ? $filter[$role] : []) + (isset($filter['*']) ? $filter['*'] : []);
        if (!$filter) {
            return true;
        }
		//require
		if (isset($filter['require'])) {
			$valid = Validators::requireValidate($filter['require'], $this->params);
			if ($valid !== true) {
			    if (is_array($valid)) {
                    return [$valid[0] => $valid[1]];
                }
				return  [$valid => self::ERR_EMPTY];
			}
		}
		if (isset($filter['require-file']) && ($requires = array_diff($filter['require-file'], array_keys($_FILES)))) {
			return array_fill_keys($requires, self::ERR_EMPTY);
		}
		//range
		if (isset($filter['range'])) {
			foreach ($filter['range'] as $param => $range) {
				if (!key_exists($param, $this->params)) {
					continue;
				}
				if (is_string($range)) {
					$inRange = $this->$range();
				} else {
					$inRange = in_array($this->params[$param], $range);
				}
				if (!$inRange) {
					return [$param => self::ERR_VALID];
				}
			}
		}
		//default
		if (isset($filter['default'])) {
			if (is_string($filter['default'])) {
				$default = $filter['default'];
				$this->$default();
			} elseif (is_array($filter['default'])) {
				$this->params += $filter['default'];
			}
		}
		return true;
	}
	
	public function access()
	{
		$access = isset($this->access[$this->action]) ? $this->access[$this->action] : (isset($this->access['*']) ? $this->access['*'] : null);
		if (!$access) {
			return true;
		}
		//allow anyone both ? and @
		if (in_array('*', $access, true)) {
			return true;
		}
		$role = $this->user->getRole();
		if ($role == '?') {
			if (in_array($role, $access, true)) {
				return true;
			} else {
				return false;
			}
		} else {
			if (in_array('@', $access, true) || in_array($role, $access, true)) {
				return true;
			} else {
				return false;
			}
		}
	}
	
	public function page($must = true) {
		if (isset($_GET[self::PAGE_PARAM])) {
			$this->page = (int)$_GET[self::PAGE_PARAM];
			$this->pagesize = self::PAGESIZE;
		}
		if (isset($_GET[self::PAGESIEZE_PARAM])) {
			$this->pagesize = (int)$_GET[self::PAGESIEZE_PARAM];
			$this->pagesize <= static::MAX_PAGESIZE ?: $this->pagesize = static::MAX_PAGESIZE;
		}
		if ($must) {
			$this->pagesize != 0 ?: $this->pagesize = self::PAGESIZE;
			$this->page != 0 ?: $this->page = 1;
		}
	}
	
	protected function response($res, $errTrait = null, $statusCode = 200, $text = null)
	{
		$this->response->format = Response::FORMAT_JSON;
		
		$error = ($errTrait === null ? [] : $errTrait->errors()) ?: $this->errors;
		
		if ($res === null) {
			if (in_array(ERR_SERVER, $error) || !$error) {
				$this->response->setStatusCode(500);
			} else {
				$this->response->setStatusCode($statusCode > 400 ? $statusCode : 400);
			}
			$res = ['error' => $error, 'error_msg' => $errTrait ? $errTrait->lastErrorMsg() : ''];
		} else {
			switch ($statusCode) {
				case 201 : 
				case 204 : 
					if (ENV === 'dev' && DEBUG === true && ENV_SIDE === 'backend') {
						$statusCode = 200;
					}
				case 200 : 
				default:
					$this->response->setStatusCode($statusCode);
			}
		}
		
		return $res;
	}
}