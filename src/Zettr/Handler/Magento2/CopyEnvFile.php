<?php

namespace Zettr\Handler\Magento2;

use Zettr\Handler\AbstractHandler;
use Zettr\Handler\HandlerInterface;
use Zettr\Message;

class CopyEnvFile extends AbstractHandler {

    /**
     * Apply
     *
     * @throws \Exception
     * @return bool
     */
    protected function _apply() {

        // let's use some speaking variable names... :)
        $sourceFile = $this->value;
        $targetFile = $this->param1;

        if (empty($sourceFile)) {
            $this->setStatus(HandlerInterface::STATUS_SKIPPED);
            return true;
        }

        if (!is_file($sourceFile)) {
            throw new \Exception(sprintf('Source file "%s" does not exist', $targetFile));
        }

        if (is_file($targetFile)){
            $this->setStatus(HandlerInterface::STATUS_ALREADYINPLACE);
            $this->addMessage(new Message(
                sprintf('File "%s" already exists. Will not be overwritten.', $targetFile),
                Message::SKIPPED
            ));
        } else {
            $res = copy($sourceFile, $targetFile);
            if ($res) {
                $this->setStatus(HandlerInterface::STATUS_DONE);
                $this->addMessage(new Message(
                    sprintf('Successfully copied file "%s" to "%s"', $sourceFile, $targetFile),
                    Message::OK
                ));
            } else {
                $this->setStatus(HandlerInterface::STATUS_ERROR);
                $this->addMessage(new Message(
                    sprintf('Error while copying file "%s" to "%s"', $sourceFile, $targetFile),
                    Message::ERROR
                ));
            }
        }

        return true;
    }


}