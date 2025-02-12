import CryptoJS from "crypto-js";
const USER_KEY = "user";

// Menyimpan user ke localStorage
export const saveUser = (user) => {
  localStorage.setItem(USER_KEY, JSON.stringify(user));
};

// Mendapatkan user dari localStorage
export const getUser = () => {
  const storedUser = localStorage.getItem(USER_KEY);
  return storedUser ? JSON.parse(storedUser) : null;
};

export const getTokens = () => {
  const encryptedToken = localStorage.getItem("token_");
  if (!encryptedToken) return null;
  return CryptoJS.AES.decrypt(encryptedToken, "SECRET_KEY").toString(CryptoJS.enc.Utf8);
}

// Menghapus user dari localStorage
export const removeUser = () => {
  localStorage.removeItem(USER_KEY);
};
