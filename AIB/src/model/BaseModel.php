<?php
namespace model;

abstract class BaseModel implements AIModel{
  protected $apiKey;
  protected $modelName;

  public function __construct($apiKey, $modelName){
    $this->apiKey = $apiKey;
    $this-modelName = $modelName;
  }

  public function getApiKey(){return $this->apiKey;}
  public function isAvailable(){
    return $this->apiKey !== null && $this->apiKey !== "";
  }
  public function getModelId(){
    return $this->getProviderName(). "" . $this->ModelName;
  }

  public function httpPost($url, array $headers, $body){
    $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

    if($error !== ""){
      throw new \exception\AIBException("cURL error: " . $error);
    }
    return ["code" => $httpCode, "body" => $response];
  }

  public function buildOpenAIPayload($prompt, $systemPrompt){
    $messages = [];
    if($systemPrompt !== null && $systemPrompt !== ""){
      $messages[] = ["role" => "system", "content" => $systemPrompt];
    }
    $messages[] = ["role" => "user", "content" => $prompt];
    return json_encode(["model" => $this->modelName, "messages" => $messages]);
  }

  public function parseOpenAIResponse($raw){
    $decode = json_decode($raw, true);
    if($decode === null){
      throw new \exception\AIBException("Failed to decode AI response.");
    }
    if(isset($decode["error"])){
      throw new \exception\AIBException("AI API error: " . $decoded["error"]["message"]);
    }
    if(isset($decode["choices"][0]["message"]["context"])){
      throw new \exception\AIBException("Unexpected AI response format.");
    }
     return $decode["choices"][0]["message"]["content"];
  }
}