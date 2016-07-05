<?php

namespace Routes;

class RoutableTarget 
{
    public function actionIndexAny() {}
    public function actionIndexDelete() {}
    public function actionIndexGet() {}
    public function actionIndexHead() {}
    public function actionIndexOptions() {}
    public function actionIndexPost() {}
    public function actionIndexPut() {}
    public function actionPageContactGet() {}
    public function actionIndexTrace() {}

    public function invalidMethod() {}

    public function anotherInvalidRoutableMethod() {}
}