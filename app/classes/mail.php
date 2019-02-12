<?php

namespace Classes;

class Mail {
    
    public $tpl;
    public $subject;
    public $params;
    public $to;
    
    public function send()
    {
        $f3 = \Base::instance();
        $fp = $f3->get('ROOT').'/'.$f3->get('UI').'mail/'.$this->tpl.'.php';
        $from = $f3->get('EMAIL_FROM');
        $params = [];
        foreach ($this->params as $k => $v) {
            $params['{{'.$k.'}}'] = htmlspecialchars($v);
        }        
        $body = str_replace(array_keys($params), array_values($params), file_get_contents($fp));
        $header = "MIME-Version: 1.0\n";
        $header .= "Content-type: text/html; charset=utf-8\n"; 
        if ($from) {
            $header .= "From: Patient rosters management <$from>\n";
        }
        
        return mail($this->to, $this->subject, $body, $header);
    }
    
}