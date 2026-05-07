<?php
namespace model;

class ChatGPTModel extends BaseModel{

    public function getProviderName(){
        return "chatgpt";
    }
    public function query($prompt, $systemPrompt = null){
        $payload = $this->buildOpenAIPayload($prompt, $systemPrompt);
        $result = $this->httpPost(
"https://api.openai.com/v1/chat/completions",
            [
                "Content-Type: application/json",
                "Authorization: Bearer " . $this->apiKey
            ],
            $payload
        );
        if($result["code"] !== 200){
            throw new \aib\exception\AIBException("ChatGPT returned HTTP " . $result["code"] . ": " . $result["body"]);
        }

        return $this->parseOpenAIResponse($result["body"]);
    }
}
