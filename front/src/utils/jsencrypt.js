/**
 * @description RSA加解密工具 - 前端敏感信息加密传输
 * @description 使用JSEncrypt库实现RSA非对称加解密，用于登录密码等敏感信息的加密传输。
 * 加密流程：前端使用公钥加密 → 传输密文 → 后端使用私钥解密
 * 安全注意：私钥硬编码在前端代码中仅适用于低安全场景，生产环境应仅保留公钥用于加密，
 * 解密操作应由后端完成。密钥对可通过 http://web.chacuo.net/netrsakeypair 生成
 */
import JSEncrypt from 'jsencrypt/bin/jsencrypt.min'

const publicKey = 'MFwwDQYJKoZIhvcNAQEBBQADSwAwSAJBAKoR8mX0rGKLqzcWmOzbfj64K8ZIgOdH\n' +
  'nzkXSOVOZbFu/TJhZ7rFAN+eaGkl3C4buccQd/EjEsj9ir7ijT7h96MCAwEAAQ=='

const privateKey = 'MIIBVAIBADANBgkqhkiG9w0BAQEFAASCAT4wggE6AgEAAkEAqhHyZfSsYourNxaY\n' +
  '7Nt+PrgrxkiA50efORdI5U5lsW79MmFnusUA355oaSXcLhu5xxB38SMSyP2KvuKN\n' +
  'PuH3owIDAQABAkAfoiLyL+Z4lf4Myxk6xUDgLaWGximj20CUf+5BKKnlrK+Ed8gA\n' +
  'kM0HqoTt2UZwA5E2MzS4EI2gjfQhz5X28uqxAiEA3wNFxfrCZlSZHb0gn2zDpWow\n' +
  'cSxQAgiCstxGUoOqlW8CIQDDOerGKH5OmCJ4Z21v+F25WaHYPxCFMvwxpcw99Ecv\n' +
  'DQIgIdhDTIqD2jfYjPTY8Jj3EDGPbH2HHuffvflECt3Ek60CIQCFRlCkHpi7hthh\n' +
  'YhovyloRYsM+IS9h/0BzlEAuO0ktMQIgSPT3aFAgJYwKpqRYKlLDVcflZFCKY7u3\n' +
  'UP8iWi1Qw0Y='

/**
 * 使用RSA公钥加密明文，用于登录密码等敏感信息传输前的加密
 * @param {string} txt - 需要加密的明文字符串（如登录密码）
 * @returns {string|false} 加密后的Base64密文，加密失败返回false
 */
export function encrypt(txt) {
  const encryptor = new JSEncrypt()
  encryptor.setPublicKey(publicKey)
  return encryptor.encrypt(txt)
}

/**
 * 使用RSA私钥解密密文，用于前端需要还原加密数据的场景
 * @param {string} txt - 需要解密的Base64密文
 * @returns {string|false} 解密后的明文字符串，解密失败返回false
 */
export function decrypt(txt) {
  const encryptor = new JSEncrypt()
  encryptor.setPrivateKey(privateKey)
  return encryptor.decrypt(txt)
}
