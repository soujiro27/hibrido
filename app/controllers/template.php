<?php
namespace App\Controllers;

use Twig_Loader_Filesystem;

class template {
    protected $templateEngine;
    public function __construct() {
        $loader = new Twig_Loader_Filesystem('./hibrido/views/');
        $this->templateEngine = new \Twig_Environment($loader, [
            'debug' => true,
            'cache' => false
        ]);

        $this->templateEngine->addFilter(new \Twig_SimpleFilter('trim',function($cadena){
            return trim($cadena);
        }));


        $this->templateEngine->addFilter(new \Twig_SimpleFilter('hora',function($cadena){
            return substr($cadena,0,-11);

        }));


        $this->templateEngine->addFilter(new \Twig_SimpleFilter('extension',function($cadena){
            
            $extension = explode('.',$cadena);
            return $extension[1];

        }));
    }

    public function render($fileName, $data = []) {
        return $this->templateEngine->render($fileName, $data);
    }


   

}