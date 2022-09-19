var translate = (function (axios) {
    'use strict';

    function _interopDefaultLegacy (e) { return e && typeof e === 'object' && 'default' in e ? e : { 'default': e }; }

    var axios__default = /*#__PURE__*/_interopDefaultLegacy(axios);

    /*! *****************************************************************************
    Copyright (c) Microsoft Corporation.

    Permission to use, copy, modify, and/or distribute this software for any
    purpose with or without fee is hereby granted.

    THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
    REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
    INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
    LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
    OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
    PERFORMANCE OF THIS SOFTWARE.
    ***************************************************************************** */

    function __awaiter(thisArg, _arguments, P, generator) {
        function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
        return new (P || (P = Promise))(function (resolve, reject) {
            function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
            function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
            function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
            step((generator = generator.apply(thisArg, _arguments || [])).next());
        });
    }

    /**
     * Translate a text and return the result.
     * @param {String} text The text to be translated.
     * @param {Object} options The options.
     * @param {String} options.from The lang of the text being translated.
     * @param {String} options.to The lang in which the text should be translated.
     * @throws {TypeError} If the first parameter is not a string.
     * @throws {TypeError} If the second parameter is not an Object.
     * @throws {TypeError} If the second parameter is missing the "from" key.
     * @throws {TypeError} If the second parameter is missing the "to" key.
     * @throws {TypeError} If the second parameter key "from" is not a string.
     * @throws {TypeError} If the second parameter key "to" is not a string.
     * @return {Promise<String>}
     */
    const translate = (text, options) => __awaiter(void 0, void 0, void 0, function* () {
        if (typeof text !== "string") {
            throw new TypeError("expected parameter text to be a string");
        }
        if (!(options instanceof Object)) {
            throw new TypeError("expected parameter options to be an object");
        }
        if (!("from" in options)) {
            throw new TypeError('expected parameter options to contain the key "from"');
        }
        if (!("to" in options)) {
            throw new TypeError('expected parameter options to contain the key "to"');
        }
        if (typeof options.from !== "string") {
            throw new TypeError("expected key options.from to be a string");
        }
        if (typeof options.to !== "string") {
            throw new TypeError("expected key options.to to be a string");
        }
        const encodedText = encodeURI(text);
        const { data } = yield axios__default['default'].get(`https://translate.googleapis.com/translate_a/single?client=gtx&sl=${options.from}&tl=${options.to}&dt=t&q=${encodedText}`);
        // @ts-ignore
        // Too lazy to create a type for this kind of Array:
        // [ 'Bonjour\n', 'Hello\n', null, null, 1 ]
        return data[0].map((line) => line[0]).join("");
    });

    return translate;

}(axios));
