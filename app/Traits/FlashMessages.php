<?php

namespace App\Traits;

trait FlashMessages
{
    private $errorMessages = [];

    private $successMessages = [];

    private $infoMessages = [];

    private $warningMessages = [];

    protected function setFlashMessage($message, $type)
    {
        $model = 'infoMessages';

        switch ($type) {
            case 'error':
                $model = 'errorMessages';
                break;

            case 'success':
                $model = 'successMessages';
                break;

            case 'info':
                $model = 'infoMessages';
                break;

            case 'warning':
                $model = 'warningMessages';
                break;
        }

        if (is_array($message)) {
            foreach ($message as $key => $value) {
                array_push($this->$model, $value);
            }
        } else {
            array_push($this->$model, $message);
        }
    }

    protected function getFlashMessage()
    {
        return [
            'error' => $this->errorMessages,
            'success' => $this->successMessages,
            'info' => $this->infoMessages,
            'warning' => $this->warningMessages,
        ];
    }

    protected function showFlashMessages()
    {
        session()->flash('error', $this->errorMessages);
        session()->flash('success', $this->successMessages);
        session()->flash('info', $this->infoMessages);
        session()->flash('warning', $this->warningMessages);
    }
}
