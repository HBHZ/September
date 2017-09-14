<?php
/**
 * 观察者模式
 * @author: Mac
 * @date: 2012/02/22
 */


class Paper{ /* 主题    */
    private $_observers = array();
    
    public function register($sub){ /*  注册观察者 */
        $this->_observers[] = $sub;
    }
    
    
    public function trigger(){  /*  外部统一访问    */
        if(!empty($this->_observers)){
            foreach($this->_observers as $observer){
                $observer->update();
            }
        }
    }
}

/**
 * 观察者要实现的接口
 */
interface Observerable{
    public function update();
}

class Subscriber implements Observerable{
    public function update(){
        echo "Callback\n";
    }
}