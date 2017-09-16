<?php
// 题外：php 钩子：http://www.cnblogs.com/baochuan/p/6290504.html


/**

*
* 作为程序员，设计出优雅而完美的系统，永远是让我们非常兴奋的事情。高手不在于你会多少语言，而在于你有多高的思想。

　　在设计中，怎么体现自身价值，那就是要比别人多想几步。
　　
　　　　讲钩子程序，起源是对用户提交的参数校验（永远不要相信用户），一开始为了赶工期，按照比较传统的方式，每个接口里重复性的对参数进行过滤。后面随着业务的发展（功能迭代），系统的维护成本越来越高，遂想一个更高级的方式进行处理。借鉴同事之前的代码，使用钩子方式进行重构。
　　　　
　　　　　　之前写过javascript 钩子机制, 偏后钩，可以互相借鉴下。
　　　　　　*/

/**


　　把一段程序块（执行体）通过某种方式挂入系统中，从而获得对系统的控制权。
　　
　　注意下图挂钩位置：
　　*/

/**



　
　　小的方面： 进行基础的入参校验或消息过滤。
　　　　大的方面：组件化，可在系统中进行插拔管理。
　　　　　　
　　　　　　优点：
　　　　　　　　　　1、降低系统的耦合度；
　　　　　　　　　　　　　　2、降低开发、测试人力成本，用少量的代码实现高可用功能；
　　　　　　　　　　　　　　　　　　3、提高模块间的可用性；
　　　　　　　　　　　　　　　　　　　　　　4、通过配置（配置文件or数据库）的方式升级接口。
　　　　　　　　　　　　　　　　　　　　　　　　缺点：
　　　　　　　　　　　　　　　　　　　　　　　　　　　　学习成本过高；
　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　系统复杂度提升；
　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　*/


/*


配置文件的方式进行钩子定义、钩子链管理（使用“组”的概念）、挂钩。

　　　　钩子：程序执行体；
　　　　　　　   钩子组： 钩子链的分类定义；
　　　　　　　   挂钩： 入口（MVC中action或者controller）与钩子组进行绑定。
　　　　　　　   
　　　　　　　   
　　　　　　　   /*
　　　　　　　   *
　　　　　　　   *
　　　　　　　   *
　　　　　　　   *
　　　　　　　   *
　　　　　　　   */



/**
 * @name Service_Page_Test
 * @desc page层对接第三方抽象类
 * @author
 */
abstract class Service_Page_Test
{
    public $hookGroupPrev = null; // 前钩子组
    public $hookGroupAfter = null; // 后钩子组
    public $hookReturn = array(); //钩子返回值
    public $reqData = null; // page模块分析的数据
    
    /**
     * 获取需要验证的参数配置
     * @return array
     */
    public function _getCheckParams()
    {
        return array();
    }
    
    
    
    /**
     * 入口方法
     * @param array $arrInput
     * @return array
     */
    public function execute($arrInput)
    {
        $res = array(
            'errno' => Test_Errno::ERRNO_SUCCESS,
            'errmsg' => Test_Errno::$ERRMSG[Test_Errno::ERRNO_SUCCESS],
        );
        try {
            $this->_init($arrInput);
            $this->_beforeExecute();
            $res = $this->doExecute($arrInput);
            $this->_afterExecute();
        } catch (Test_Exception $e) {
            $res = array(
                'errno' => $e->getCode(),
                'errmsg' => $e->getMessage(),
            );
        } catch (Exception $e) {
            $res = array(
                'errno' => $e->getCode(),
                'errmsg' => $e->getMessage(),
            );
            
        }
        return $res;
    }
    
    
    
    /**
     * auto exec
     * @param array $arrInput
     * @throws Exception
     * @return array
     */
    protected function doExecute($arrInput){
    }
    
    
    /**
     * 获取权限信息
     * @param array $arrInput
     * @return array
     */
    public function _init($arrInput)
    {
        $pageModulesConf = Conf::getConf('page/' . get_class($this));
        $this->reqData = $arrInput;
        $this->hookGroupPrev[] = $pageModulesConf['hook_group']['prev'];
        $this->hookGroupAfter[] = $pageModulesConf['hook_group']['after'];
    }
    
    
    /**
     * 执行filter
     * @param string
     */
    public function _beforeExecute()
    {
        if (!empty($this->hookGroupPrev) && is_array($this->hookGroupPrev)) {
            foreach ($this->hookGroupPrev as $hookGroups) {
                foreach ($hookGroups as $hookGroup) {
                    $this->_executeHook($hookGroup, $this->reqData);
                }
            }
        }
    }
    
    
    /**
     * @param array $arrInput
     * @return array
     */
    public function _afterExecute()
    {
        if (!empty($this->hookGroupAfter) && is_array($this->hookGroupAfter)) {
            foreach ($this->hookGroupAfter as $hookGroups) {
                foreach ($hookGroups as $hookGroup) {
                    $this->_executeHook($hookGroup, $this->reqData);
                }
            }
        }
    }
    
    
    /**
     * 执行filter
     * @param string
     */
    public function _executeHook($hookGroup, $reqData)
    {
        
        $hookGroupConf = Conf::getConf('hook/group/' . $hookGroup);
        if(!empty($hookGroupConf)){
            foreach($hookGroupConf as $hook){
                $hookConf = Conf::getConf('hook/hook/' . $hook);
                $class = $hookConf['class'];
                $method = $hookConf['method'];
                $inputParams = isset($hookConf['getInputParams']) ? $this->{$hookConf['getInputParams']}() : null;
                if (class_exists($class)) {
                    $obj = new $class();
                    if (method_exists($obj, $method)) {
                        $this->hookReturn[$hook][] =  $obj->$method($inputParams, $reqData);
                    }
                }
            }
        }
        
    }
    
}