<?php
namespace App\Model\Behavior;


use Cake\Cache\Cache;
use Cake\Event\Event;
use Cake\Log\Log;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use ArrayObject;

class CacheResultBehavior extends Behavior{




    public function afterSave(Event $event, Entity $entity, ArrayObject $options){
        if(isset($options['cacheKey'])){
            $currentAlias = $event->getSubject()->registryAlias();
            if(is_array($options['cacheKey'])){
                foreach ($options['cacheKey'] as $alias=>$key){
                    $keyAlias = strtolower($alias);
                    if(is_array($key)){
                        foreach ($key as $k){
                            $key = $keyAlias.'_'.strtolower($k);
                            Cache::delete($key);
                        }
                    }else{
                        $key = $keyAlias.'_'.strtolower($key);
                        Cache::delete($key);
                    }
                }
            }else{
                Log::error("Invalid cache format for delete request in ".$currentAlias.' Data:'.json_encode($options['cacheKey']));
            }
        }

    }

    public function beforeFind(Event $event,Query $query,ArrayObject $options,$primary){
        if(isset($options['cacheKey'])){
            $currentAlias = $event->getSubject()->registryAlias();
            //Cache::delete($options['cache']);
            $key = strtolower($currentAlias).'_'.strtolower($options['cacheKey']);
            if (($result = Cache::read($key)) === false) {
                $result = $query->toArray();
                Cache::write($key, $result);
            }
            $query->setResult($result);
            $event->stopPropagation();
            $query->formatResults(null, true);
        }
    }
}
