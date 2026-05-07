<?php
namespace model;
class OpenRouterModel extends BaseModel{

    public function getProviderName(){
        return "openrouter";
    }
    public function query($prompt, $systemPrompt = null){
        $payload = $this->buildOpenAIPayload($prompt, $systemPrompt);
        $result = $this->httpPost(
            "https://openrouter.ai/api/v1/chat/completions",
            [
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->apiKey,
                "HTTP-Referer: https://github.com/sz_/AIB",
                "X-Title: AIB PocketMine Plugin"
            ],
            $payload
        );
        if($result["code"] !== 200){
            throw new \exception\AIBException("OpenRouter returned HTTP " . $result["code"] . ": " . $result["body"]);
        }
        return $this->parseOpenAIResponse($result["body"]);
    }
}
