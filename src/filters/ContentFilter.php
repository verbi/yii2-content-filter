<?php
namespace verbi\yii2ContentFilter\filters;

use Yii;
use yii\base\ActionFilter;
use yii\di\Instance;
use yii\web\NotFoundHttpException;
use yii\web\User;

class ContentFilter extends ActionFilter {
    
    public $user = 'user';
    
    public $filterCallback;
    
    public $ruleConfig = ['class' => 'yii\filters\AccessRule'];
    
    public $rules = [];
    
    public function init()
    {
        parent::init();
        if ($this->user !== false) {
            $this->user = Instance::ensure($this->user, User::className());
        }
        foreach ($this->rules as $i => $rule) {
            if (is_array($rule)) {
                $this->rules[$i] = Yii::createObject(array_merge($this->ruleConfig, $rule));
            }
        }
    }
    
    public function beforeAction($action)
    {
        $user = $this->user;
        $request = Yii::$app->getRequest();
        
        foreach ($this->rules as $rule) {
            if ($allow = $rule->allows($action, $user, $request)) {
                return true;
            } elseif ($allow === false) {
                if (isset($rule->filterCallback)) {
                    call_user_func($rule->filterCallback, $rule, $action);
                } elseif ($this->filterCallback !== null) {
                    call_user_func($this->filterCallback, $rule, $action);
                } else {
                    $this->filterContent($user);
                }
                return false;
            }
        }
        if ($this->filterCallback !== null) {
            call_user_func($this->filterCallback, null, $action);
        } else {
            $this->filterContent($user);
        }
        return false;
    }

    
    protected function filterContent($user)
    {
        throw new NotFoundHttpException();
    }
}
