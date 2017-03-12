<?php

namespace spiritdead\yii2resque\components\filters;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\web\Controller;
use yii\web\Response;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\BadRequestHttpException;

class AjaxControl extends ActionFilter
{
    /**
     * Declares event handlers for the [[owner]]'s events.
     * @return array events (array keys) and the corresponding event handler methods (array values).
     */
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    /**
     * @param Action $event
     * @return boolean
     * @throws MethodNotAllowedHttpException when the request method is not allowed.
     */
    public function beforeAction($event)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (Yii::$app->request->isAjax) {
            return parent::beforeAction($event);
        } else {
            return $this->denyAccess(Yii::$app->user);
        }
    }

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     * @param Yii\web\User $user the current user
     * @throws Yii\web\ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess($user)
    {
        if ($user->getIsGuest()) {
            $user->loginRequired();
        } else {
            $this->ajaxOnly();
        }
    }

    /**
     * @return $this
     * @throws BadRequestHttpException
     */
    public function ajaxOnly()
    {
        throw new BadRequestHttpException();
    }
}