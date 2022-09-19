import { IOptions } from "./interfaces";
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
declare const translate: (text: string, options: IOptions) => Promise<string>;
export default translate;
