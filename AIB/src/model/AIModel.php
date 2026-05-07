<?php

namespace model;
interface AIModel{

    public function getModelId();
    public function getProviderName();
    public function query($prompt, $systemPrompt = null);
    public function getApiKey();
    public function isAvailable();
}
