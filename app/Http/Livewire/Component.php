<?php

namespace App\Http\Livewire;

use App\Api\Exceptions\ValidationException;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public function callMethod($method, $params = [], $captureReturnValueCallback = null)
    {
        try {
            parent::callMethod($method, $params, $captureReturnValueCallback);
        } catch (ValidationException $e) {
            $this->handleValidationException($e);
        }
    }

    private function handleValidationException(ValidationException $e): self
    {
        // TODO flash message

        foreach ($e->getErrors() as $field => $errors) {
            foreach ($errors as $error) {
                $this->addError($field, $error);
            }
        }

        return $this;
    }
}
