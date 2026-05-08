# AIB - Artificial Intelligence Backend Protocol

AIB is a PocketMine-MP plugin that provides a standardized protocol for integrating AI language models into other plugins. It abstracts away the API differences between providers so developers can query AI with a single method call.

---

## Requirements

- PocketMine-MP API 2.0.0
- PHP 7
- Minecraft PE 0.15.10
- curl extension enabled on your bin

---

## Supported Providers

All providers listed below offer free API keys or a free tier.

| Provider | Site | Free Tier |
|---|---|---|
| OpenRouter | https://openrouter.ai | Yes, many free models |
| Groq | https://console.groq.com | Yes, free with rate limits |
| Together AI | https://api.together.xyz | Yes, free credits on signup |
| Mistral | https://console.mistral.ai | Yes, free tier available |
| Gemini | https://aistudio.google.com | Yes, free via Google AI Studio |
| ChatGPT | https://platform.openai.com | Paid API, no free tier |

---

## Installation

1. Drop the AIB plugin into your `plugins/` folder.
2. Start the server once to generate `config.yml`.
3. Open `plugins/AIB/config.yml` and fill in your API key(s).
4. Restart the server.

---

## Configuration

```yaml
default_model: "openrouter:meta-llama/llama-3.1-8b-instruct:free"

models:
  openrouter:
    api_key: "your-key-here"
    model: "meta-llama/llama-3.1-8b-instruct:free"

  groq:
    api_key: "your-key-here"
    model: "llama-3.1-8b-instant"

  together:
    api_key: "your-key-here"
    model: "meta-llama/Llama-3.2-11B-Vision-Instruct-Turbo"

  mistral:
    api_key: "your-key-here"
    model: "mistral-small-latest"

  gemini:
    api_key: "your-key-here"
    model: "gemini-1.5-flash"

  chatgpt:
    api_key: "your-key-here"
    model: "gpt-4o-mini"
```

Only fill in the providers you want to use. Providers with an empty `api_key` are skipped.

The `default_model` value must match the format `provider:modelname` exactly as it appears in the registered model list.

---

## Commands

| Command | Permission | Description |
|---|---|---|
| /aib info | aib.command | Shows version and default model |
| /aib models | aib.command | Lists all registered models |
| /aib query \<text\> | aib.command | Sends a query to the default model |

---

## Developer API

### Getting the AIB instance

```php
$aib = Loader::getInstance();
```

### Async query (recommended)

Runs in a background thread. Does not block the main thread.

```php
Loader::getInstance()->query(
    "What is the capital of France?",
    null,
    null,
    function($response, $e) use ($p){
        if ($e !== null){
            $p->sendMessage("Error: " . $e);
            return;
        }
        $p->sendMessage("AI says: " . $response);
    }
);
```

### Async query with a specific model

```php
Loader::getInstance()->query(
    "Tell me a joke.",
    "groq:llama-3.1-8b-instant",
    "You are a funny comedian.",
    function($response, $e) use ($p){
        if ($e !== null){
            $p->sendMessage("Error: " . $e);
            return;
        }
        $p->sendMessage($response);
    }
);
```

### Sync query (blocking)

Only use this inside an AsyncTask. Do not call this on the main thread.

```php
try{
    $response = Loader::getInstance()->querySync(
        "What is 2 + 2?",
        "openrouter:meta-llama/llama-3.1-8b-instruct:free",
        null
    );
    echo $response;
} catch (exception\AIBException $ex){
    echo "Error: " . $e-x>getMessage();
}
```

### Registering a custom model

You can register your own model by implementing `model\AIModel` or extending `model\BaseModel`.

```php
class MyCustomModel extends aib\model\BaseModel{

    public function getProviderName(){
        return "myprovider";
    }

    public function query($prompt, $systemPrompt = null){
        // your HTTP call here
        // return a string response
    }
}

$model = new MyCustomModel("my-api-key", "my-model-name");
Loader::getInstance()->registerModel($model);
```

### Listening to AI responses via event

```php
public function onAIResponse(event\AIResponseEvent $ev){
    if($ev->hasError()){
        $this->getLogger()->warning("AI error: " . $ev->getError());
        return;
    }
    $this->getLogger()->info("AI responded to: " . $ev->getPrompt());
}
```

Register the listener normally in your plugin's `onEnable`.

---

## Method Reference

### AIBPlugin

| Method | Parameters | Returns | Description |
|---|---|---|---|
| getInstance() | none | AIBPlugin | Gets the static plugin instance |
| query() | prompt, modelId, systemPrompt, callback | void | Sends an async AI query |
| querySync() | prompt, modelId, systemPrompt | string | Sends a blocking AI query |
| registerModel() | AIModel | void | Registers a custom model |
| getModel() | modelId | AIModel or null | Gets a model by ID |
| getDefaultModel() | none | AIModel or null | Gets the configured default model |
| getRegisteredModels() | none | array | Gets all registered models |

### AIModel / BaseModel

| Method | Returns | Description |
|---|---|---|
| getModelId() | string | Returns provider:modelname |
| getProviderName() | string | Returns the provider slug |
| query(prompt, systemPrompt) | string | Executes a query synchronously |
| getApiKey() | string | Returns the configured API key |
| isAvailable() | bool | Returns true if API key is set |

---

## Model ID Format

Model IDs follow the format `provider:modelname`. Examples:

- `openrouter:meta-llama/llama-3.1-8b-instruct:free`
- `groq:llama-3.1-8b-instant`
- `together:meta-llama/Llama-3.2-11B-Vision-Instruct-Turbo`
- `mistral:mistral-small-latest`
- `gemini:gemini-2.5-flash`
- `chatgpt:gpt-4o-mini`

---

## Notes

- Queries are executed asynchronously to avoid blocking the server tick.
- The sync method `querySync` is provided for use inside `AsyncTask::onRun()` only.
- curl must be enabled in your PHP build. Most PocketMine PHP binaries include it.
- SSL verification is disabled for compatibility with older PHP 5.6 builds.
- If no provider has an API key configured, all queries will throw an `AIBException`.

---

## Author

Developed By SevenZero (ign: sz_)

README written by chatgpt
