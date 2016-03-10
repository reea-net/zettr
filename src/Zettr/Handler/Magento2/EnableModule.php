<?php

namespace Zettr\Handler\Magento2;

use Zettr\Handler\HandlerInterface;
use Zettr\Message;

class EnableModule extends AbstractDatabase {

    /**
     * Apply
     *
     * @throws Exception
     * @return bool
     */
    protected function _apply() {


        $localConfigFile = 'app/etc/config.php';


        $module = $this->param1;

        if (!is_file($localConfigFile)) {
            throw new \Exception(sprintf('File "%s" does not exist', $localConfigFile));
        }
        if (!is_writable($localConfigFile)) {
            throw new \Exception(sprintf('File "%s" is not writeable', $localConfigFile));
        }
        if (empty($module)) {
            throw new \Exception('No module supplied');
        }
        if (!empty($this->param2)) {
            throw new \Exception('Param2 is not used in this handler and must be empty');
        }
        if (!empty($this->param3)) {
            throw new \Exception('Param3 is not used in this handler and must be empty');
        }

        $fileContent = require $localConfigFile;
        if ($fileContent === false) {

            throw new \Exception(sprintf('Error while reading file "%s"', $localConfigFile));
        }

        if(!isset($fileContent['modules'][$module])){
            throw new \Exception(sprintf('Module %s is not defined in "%s"', $module, $localConfigFile));
        }

        $changes=0;

        if($this->value==$fileContent['modules'][$module]){
            $this->addMessage(new Message(sprintf('Value "%s" is already in place. Skipping.', $this->value), Message::SKIPPED));
        }else{
            $this->addMessage(new Message(sprintf('Updated value from "%s" to "%s"', $fileContent['modules'][$module], $this->value)));
            $fileContent['modules'][$module] = (int)$this->value;
            $changes++;
        }

        if ($changes > 0) {

            $contents = "<?php\nreturn " . var_export($fileContent, true) . ";\n";

            $res = file_put_contents($localConfigFile, $contents);
            if ($res === false) {
                throw new \Exception(sprintf('Error while writing file "%s"', $localConfigFile));
            }
            $this->setStatus(HandlerInterface::STATUS_DONE);
        } else {
            $this->setStatus(HandlerInterface::STATUS_ALREADYINPLACE);
        }

        return true;
    }
}
