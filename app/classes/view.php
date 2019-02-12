<?php

namespace Classes;

use Models\User;

class View extends \View {
    
    protected $vars = [];

    public $messagePrefix = 'message';
    public $messages = ['Common', 'Error'];
    
    public function show($name=false) {
        $f3 = \Base::instance();
        $f3->copy('CSRF','SESSION.csrf');
        $this->extractVars();
        if ($f3->ajax() || !$name) {
            $this->vars['CSRF'] = $f3->CSRF;
            echo json_encode($this->vars);
        } else {
            echo $this->render('layouts/'.$f3->get('layout').'/header.php');
            echo $this->render($name.'.php');
            echo $this->render('layouts/'.$f3->get('layout').'/footer.php');
        }
    }
    
    public function setvar($var, $val) {
        $this->vars[$var] = $val;
    }

    public function getvar($var) {
        if (!isset($this->vars[$var])) {
            return false;
        }
        return $this->vars[$var];
    }

    public function clearvar($var) {
        unset($this->vars[$var]);
    }
    
    protected function extractVars() {
        $f3 = \Base::instance();
        if (is_array($this->vars)) foreach ($this->vars as $var => $val) {
            if ($var) $f3->set($var, $val);        
        }
    }

    function setMessage($name, $content) {
        $view = $this;
        if (!in_array($name, $this->messages)) {
            return false;
        }
        $view->setvar($this->messagePrefix.$name, $content);
    }

    function setSessionMessage($name, $content) {
        $f3 = \Base::instance();
        if (!in_array($name, $this->messages)) {
            return false;
        }
        $f3->set('SESSION.'.$this->messagePrefix.$name, $content);
    }
    
    function extractSessionMessages() {
        $f3 = \Base::instance();
        $view = $this;
        foreach ($this->messages as $name) {
            $var = $this->messagePrefix.$name;
            $var_session = 'SESSION.'.$var;
            $view->setvar($var, $f3->get($var_session));
            $f3->clear($var_session);
        }
    }

    
}

