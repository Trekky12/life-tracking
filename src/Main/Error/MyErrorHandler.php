<?php

namespace App\Main\Error;

use Slim\Handlers\ErrorHandler;

class MyErrorHandler extends ErrorHandler {

    protected function determineStatusCode(): int {
        /* if ($this->exception instanceof \Exception) {
          return $this->exception->getCode();
          }
          return parent::determineStatusCode();
         */
        return 200;
    }

}
