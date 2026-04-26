export function isEmpty(value) {
  return value === null || value === '' || value === undefined
}

export function isHttp(url) {
  return url && (url.indexOf('http://') !== -1 || url.indexOf('https://') !== -1)
}

export function isExternal(path) {
  return /^(https?:|mailto:|tel:)/.test(path)
}

export function validEmail(email) {
  const reg = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/
  return reg.test(email)
}
