<?php

namespace Zettr\Handler;

use Zettr\Message;

class EnvFile extends AbstractHandler {

    /**
     * Apply
     *
     * @throws Exception
     * @return bool
     */
    protected function _apply() {
         
         
        $file = $this->param1;
        $expression = $this->param2;

        if (!is_file($file)) {
            throw new \Exception(sprintf('File "%s" does not exist', $file));
        }
        if (!is_writable($file)) {
            throw new \Exception(sprintf('File "%s" is not writeable', $file));
        }
        if (empty($expression)) {
            throw new \Exception('No xpath defined');
        }
        if (!empty($this->param3)) {
            throw new \Exception('Param3 is not used in this handler and must be empty');
        }

        $fileContent = require $file;
        if ($fileContent === false) {
            
            throw new \Exception(sprintf('Error while reading file "%s"', $file));
        }
             
        
        $find = $this->search($fileContent,$expression);
        $changes = 0;
        
        if($find){
            if($find['value']==$this->value){
                $this->addMessage(new Message(sprintf('Value "%s" is already in place. Skipping.', $this->value), Message::SKIPPED));
            }else{
                $this->addMessage(new Message(sprintf('Updated value from "%s" to "%s"', $find['value'], $this->value)));
                $data = $this->stringToArray($find['path'],$this->value);
                $fileContent = array_replace_recursive($fileContent,$data);
                $changes++;
            }
        }else{
            $this->setStatus(HandlerInterface::STATUS_SUBJECTNOTFOUND);
            throw new \Exception(sprintf('No config elements found', $expression));
        }
        

        if ($changes > 0) {
            
            $contents = "<?php\nreturn " . var_export($fileContent, true) . ";\n";
            
            $res = file_put_contents($file, $contents);
            if ($res === false) {
                throw new \Exception(sprintf('Error while writing file "%s"', $file));
            }
            $this->setStatus(HandlerInterface::STATUS_DONE);
        } else {
            $this->setStatus(HandlerInterface::STATUS_ALREADYINPLACE);
        }

        return true;
    }

    function search($array, $searchKey=''){
        $iter = new \RecursiveIteratorIterator(
            new \RecursiveArrayIterator($array),
            \RecursiveIteratorIterator::SELF_FIRST);
    
        foreach ($iter as $key => $value) {
            if ($key === $searchKey) {
                $keys = array($key);
                for($i=$iter->getDepth()-1;$i>=0;$i--){
                    array_unshift($keys, $iter->getSubIterator($i)->key());
                }
                return array('path'=>implode('.', $keys), 'value'=>$value);
            }
        }
        return false;
    }
    
    function stringToArray($path='',$value='')
    {
        $separator = '.';
        $pos = strpos($path, $separator);
        if ($pos === false) {
            return array($path=>$value);
        }
    
        $key = substr($path, 0, $pos);
        $path = substr($path, $pos + 1);
        
        $result = array(
            $key => $this->stringToArray($path,$value),
        );
    
        return $result;
    }
}
