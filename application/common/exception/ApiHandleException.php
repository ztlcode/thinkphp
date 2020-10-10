<?php 
namespace app\common\exception;

use Exception;
use think\exception\ErrorException;
use think\exception\PDOException;
use think\exception\Handle;
use think\exception\HttpException;
use think\facade\Log;

class ApiHandleException extends Handle
{
    public function render(Exception $e)
    {
        if(config('app_debug')){
            return parent::render($e);
        }else{
            $log = [
                'apiError' => $this->getApiError($e),
                'getData'  => $_GET,
                'postData' => $_POST,
                'headerData' => $_SERVER
            ];
            //记录日志
            $this->recordErrorLog($log);

            if($e instanceof HttpException){
                return json(['msg'=>'请求错误','code'=>400]);
            }
            if($e instanceof ErrorException){
                return json(['msg'=>'服务异常','code'=>500]);
            }
            if($e instanceof PDOException){
                return json(['msg'=>'SQL异常','code'=>600]);
            }
        }
    }

    private function getApiError($e)
    {
        $data = [];
        if($e instanceof HttpException){
            $data['msg'] = $e->getMessage();
        }
        if($e instanceof ErrorException){
            $data = [
                'msg'  => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ];
        }
        if($e instanceof PDOException){
            $data['msg'] = $e->getData('Database Status');
        }

        return $data;
    }

    private function recordErrorLog($data)
    {
        Log::record($data, 'error');
    }
}