<?php

namespace Hutchh\VerificationRule\Payload;
use Hutchh\VerificationRule\Exceptions\InvalidAttributeException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
abstract class abstractBuilder{

    protected $attributes = [];
    protected $fillable = [];
    protected $haveSomeValue = FALSE;

    public function __construct(array $attributes = []){
        $this->fill($attributes);
    }

    public function fetch($key){
        return $this->attributes[$key] ?: FALSE;
    }

    public function has($key){
        return isset($this->attributes[$key]) ?: FALSE;
    }

    public function fill(array $attributes){
        $class = get_called_class();

        foreach($attributes as $key => $value){
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function isFillable($key){
        if(in_array($key, $this->fillable)){
            return TRUE;
        }

        return empty($this->fillable);
    }

    public function getAttribute($key){
        $value = $this->getAttributeFromArray($key);
        return $value;
    }

    public function setAttribute($key, $value){
        if($this->hasSetMutator($key)){
            $studyKey = $this->getStudlyCase($key);
            $method = 'set' . $studyKey . 'Attribute';
            $this->haveSomeValue = TRUE;
            try{
                return $this->{$method}($value);
            }catch (\TypeError $e) {
                do {
                    Log::info(sprintf("%s:%d %s (%d) [%s]\n", $e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), get_class($e)));
                } while($e = $e->getPrevious());
                throw new \Exception("$studyKey :: datatype mismatch");
            }
        }
        $snakeKey = Str::snake($key);
        $lowerKey = Str::lower($key);
        $camelKey = Str::camel($key);
        if($this->isFillable($key)){
            $this->attributes[$key] = $value;
        }elseif($this->isFillable($snakeKey)){
            $this->attributes[$snakeKey] = $value;
        }elseif($this->isFillable($lowerKey)){
            $this->attributes[$lowerKey] = $value;
        }elseif($this->isFillable($camelKey)){
            $this->attributes[$camelKey] = $value;
        }

        return $this;
    }

    public function hasGetMutator($key){
        return method_exists($this, 'get' . $this->getStudlyCase($key) . 'Attribute');
    }

    public function hasSetMutator($key){
        return method_exists($this, 'set' . $this->getStudlyCase($key) . 'Attribute');
    }

    public function __get($key){
        return $this->getAttribute($key);
    }

    public function __set($key, $value){
        $this->setAttribute($key, $value);
    }

    public function __isset($key){
        return isset($this->attributes[$key]) || ($this->hasGetMutator($key) && !is_null($this->getAttribute($key)));
    }

    public function __unset($key){
        unset($this->attributes[$key]);
    }

    protected function getAttributeFromArray($key){
        $snakeKey = Str::snake($key);
        $lowerKey = Str::lower($key);
        if(array_key_exists($key, $this->attributes)){
            return $this->attributes[$key];
        }elseif(array_key_exists($snakeKey, $this->attributes)){
            return $this->attributes[$snakeKey];
        }elseif(array_key_exists($lowerKey, $this->attributes)){
            return $this->attributes[$lowerKey];
        }else{
            if($this->hasGetMutator($key)){
                $method = 'get' . $this->getStudlyCase($key) . 'Attribute';
                return $this->{$method}();
            }
        }

        throw new InvalidAttributeException(sprintf("Undefined property '%s' in class '%s'", $key, get_called_class()));

        return NULL;
    }

    protected function getStudlyCase($str){
        return ucfirst(Str::studly($str));
    }

    public function toArray(){
        return $this->attributesToArray();
    }

    public function attributesToArray(){
        $attributes = $this->attributes;

        foreach($attributes as $key => $value){
            $attributes[$key] = $this->_toArrayRecursive($value);
        }

        return $attributes;
    }

    private function _toArrayRecursive($subject){
        if(is_array($subject)){
            foreach($subject as $key => $value){
                $subject[$key] = $this->_toArrayRecursive($value);
            }

            return $subject;
        }

        return $subject instanceof Arrayable ? $subject->toArray() : $subject;
    }
}
