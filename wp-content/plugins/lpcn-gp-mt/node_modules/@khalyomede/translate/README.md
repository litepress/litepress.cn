# @khalyomede/translate

Translate a string using the free translate.googleapis.com endpoint (no API key required).

[![npm](https://img.shields.io/npm/v/@khalyomede/translate)](https://www.npmjs.com/package/@khalyomede/translate) [![NPM](https://img.shields.io/npm/l/@khalyomede/translate)](https://github.com/khalyomede/translate/blob/master/LICENSE) ![Libraries.io dependency status for latest release](https://img.shields.io/librariesio/release/npm/@khalyomede/translate) ![Snyk Vulnerabilities for npm package](https://img.shields.io/snyk/vulnerabilities/npm/@khalyomede/translate) ![npm type definitions](https://img.shields.io/npm/types/@khalyomede/translate)

## Summary

- [About](#about)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)

## About

I want to build a small web app to translate a term into another (exactly like the Google Translate widget we can use when searching "XYZ in english").

The only packages on NPM I found are not working or use an API requiring an API key, which I do not want to use (because it asks for my credit card).

This package is using a public URL, which is not the same endpoint used in classic Google API. This URL has the huge advantage of not requiring any API key. The disadvantage is that it is not official (but used by several public translation services), so the result of the URL call might change (and I'll make sure when it happens to update it). It seems that the result has not changes in ages, but nothing is sure, so for huge scale project, you would not rely on this library, but rather using the official Google API.

## Requirements

- Node installed
- NPM or Yarn installed

## Installation

In your main project folder, add this package to your dependencies.

### Using NPM

```bash
npm install @khalyomede/translate
```

### Using Yarn

```bash
yarn add @khalyomede/translate
```

## Usage

- [1. Using NodeJS](#1-using-nodejs)
- [2. Using TypeScript](#2-using-typescript)
- [3. Using the browser](#3-using-the-browser)

### 1. Using NodeJS

In this example, we will simply translate the term "Hello" in French.

```javascript
const translate = require("@khalyomede/translate");

const main = async () => {
  const translation = await translate("Hello", { from: "en", to: "fr" });

  console.log("Hello in french is ", translation);
};

main();
```

### 2. Using TypeScript

This is the same example as above, but with typing support.

```typescript
import translate from "@khalyomede/translate";

const translation: string = await translate("Hello", [ from: "en", to: "fr" ]);

console.log("Hello in french is ", translation);
```

### 3. Using the browser

To work in the browser, you need to add a script tag to download Axios. Here is an example:

```html
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
  </head>
  <body>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
    <script src="https://unpkg.com/@khalyomede/translate@0.1.0/dist/index.min.js"></script>
    <script>
      translate("Hello", { from: "en", to: "fr" }).then(function (translation) {
        console.log("Hello in french is ", translation);
      });
    </script>
  </body>
</html>
```
