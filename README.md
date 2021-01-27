# interface-maniac
Given an interface, will create an interface in any other language

## Using it

`cat Interface.php | docker run -it --rm interfaces/from:php | docker run -it --rm interfaces/to:dotnet > Interface.cs`

## How to add a language

1. Fork the repo
2. Add a directory under `languages` for the language of your choice
3. Add a `from.Dockerfile` that converts from a native interface to json output
4. Add a `to.Dockerfile` that converts from a json input to a native interface
8. Add your language to `test-matrix.json`
9. Run `docker run -v /var/run/docker.sock:/var/run/docker.sock -v test-matrix.json:/test-matrix.json interfaces/tests:latest`

## JSON schema

Example:

```php
<?php

namespace Test\Example;

interface Example {
    public function a(): void;
    public function b(string $b = "ok"): string;
    public function c(Example $a): Example;
    public function d($a);
}
```

which gets converted to:

```json
{
  "namespace": "Test.Example",
  "name": "Example",
  "methods": [
    {
      "name": "a",
      "parameters": [],
      "returnType": "void"
    },
    {
      "name": "b",
      "parameters": [
        {
          "name": "b",
          "type": "string",
          "defaultValue": "\"ok\""
        }
      ],
      "returnType": "string"
    },
    {
      "name": "c",
      "parameters": [
        {
          "name": "a",
          "type": "Test.Example.Example",
          "defaultValue": null
        }
      ],
      "returnType": "Test.Example.Example"
    },
    {
      "name": "d",
      "parameters": [
        {
          "name": "a",
          "type": null,
          "defaultValue": null
        }
      ],
      "returnType": null
    }
  ]
}
```
