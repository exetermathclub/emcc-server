/*
  Functions implementing the SRP protocol, compatible with associated Python library auth.py (should be packaged with this)
  
  Documentation is provided in auth_docs.txt
  Usage examples are provided in auth_test.html

  MIT license (MIT)

  Copyright (c) 2013 Anthony Bau
  
  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE.
*/

(function(window) {
  
  var generator = int2bigInt(2, 1, 1),
      mul_param = int2bigInt(3, 1, 1),
      large_prime = str2bigInt("b62ab56bcad9e423c6f871dc6198c9f28b4672a8d9bf219693359435181e17bd1e225fd5e178968d6e074aff175f435a4f729c564b42aa5ec68df7feac1c02f226f882ed570d25beaa0ba9adeae7a7bcc83df8eeb24ef89d4370a20486416c9e5b3c351243a3a178211993053491c3f13399e4e77c4df40de0397ee315f847b3", 16, 1);

  function randInt(n) {
    if ("number" === typeof(n)) n = int2bigInt(n, 1, 1);
    var val_array = new Uint8Array(Math.ceil(bigInt2str(n, 16).length/2)), //Note: this radix-changing thing is quite hacky.
        big256 = int2bigInt(256, 1, 1),
        compn = sub(n, int2bigInt(1, 1, 1)),
        random_number;
    crypto.getRandomValues(val_array);
    random_number = int2bigInt(0, 1, 1);
    for (var i = 0; i < val_array.length; i += 1) {
      random_number = mult(random_number, big256);
      random_number = add(random_number, int2bigInt(val_array[i], 1, 1));
    }
    random_number = mod(random_number, n) //This makes it very unrandom. TODO fix! Really!
    return random_number;
  }

  function hash() {
    var hashString = "";

    for (var i = 0; i < arguments.length; i += 1) {
      if ("string" == typeof arguments[i]) hashString += arguments[i];
      else if ("object" == typeof arguments[i]) hashString += bigInt2str(arguments[i], 16);
      else hashString += arguments[i].toString();
    }

    return str2bigInt(CryptoJS.SHA512(hashString).toString(CryptoJS.enc.Hex), 16, 1);
  }

  function getVerifier(password) {
    var salt = randInt(large_prime);
    return {
      "v": powMod(generator, hash(password, salt), large_prime),
      "s": salt
    };
  }

  function generateA() {
    var a = randInt(large_prime);
    return {
      a: a,
      A: powMod(generator, a, large_prime)
    };
  }

  function generateKey(client_data, server_data) {
    var x = hash(client_data.password, server_data.salt),
        u = hash(client_data.A, server_data.B),
        B = str2bigInt(server_data.B, 16, 1, 1),
        kv = multMod(mul_param, powMod(generator, x, large_prime), large_prime), //This is mul_param * password_verifier
        S = powMod(mod(greater(B, kv) ? sub(B, kv) : sub(add(B, large_prime), kv), large_prime), add(client_data.a, multMod(u, x, large_prime)), large_prime), //This is (B - kv) ^ (a + (u * x))
        K = CryptoJS.SHA256(bigInt2str(S, 16)),
        M = hash(large_prime, hash(client_data.uname), server_data.salt, client_data.A, server_data.B);
    
    return {
      M: M,
      K: K,
      R: hash(client_data.A, M, K)
    };
  }

  function encrypt(key, message) {
    var length = message.length,
        checksum = CryptoJS.MD5(message).toString(CryptoJS.enc.Hex).toUpperCase(),
        iv = CryptoJS.enc.Hex.parse('101112131415161718191a1b1c1d1e1f'); //TODO generate random IV
    for (var i = message.length; i % 16 != 0; i += 1) {
      message += "\0";
    }
    return {
      "ciphertext": CryptoJS.AES.encrypt(message, key, {iv:iv, mode : CryptoJS.mode.CBC}).ciphertext.toString(CryptoJS.enc.Base64),
      "checksum": checksum,
      "iv": iv.toString(CryptoJS.enc.Base64),
      "length":length
    };
  }

  function decrypt(key, message) {
    var cipherparams = CryptoJS.lib.CipherParams.create({
          ciphertext: CryptoJS.enc.Base64.parse(message.ciphertext),
          iv: CryptoJS.enc.Base64.parse(message.iv),
        });
    var cleartext = CryptoJS.AES.decrypt(cipherparams, key, {iv: CryptoJS.enc.Base64.parse(message.iv)}).toString(CryptoJS.enc.Latin1).substr(0, message.length),
        checksum = CryptoJS.MD5(cleartext).toString(CryptoJS.enc.Hex).toUpperCase();
    if (checksum == message.checksum) {
      return cleartext;
    }
    else {
      //Our keys do not match or the channel is corrupted.
      return null;
    }
  }
  
  window.getVerifier = getVerifier;
  window.generateA = generateA;
  window.generateKey = generateKey;
  window.encrypt = encrypt;
  window.decrypt = decrypt;
  
}(window));
