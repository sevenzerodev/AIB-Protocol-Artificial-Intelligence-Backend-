<?php
namespace model;

interface AIModel {
    public function getModelId();
    public function getProviderName();
    public function getCodeName();
    public function query($prompt, $systemPrompt = null);
    public function queryWithHistory($prompt, $systemPrompt, $history);
    public function getApiKey();
    public function isAvailable();
}
