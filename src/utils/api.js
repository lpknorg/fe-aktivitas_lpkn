import { toast } from 'react-toastify';
// src/utils/api.js
export const apiRequest = async (url, method = "GET", body = null, token = null) => {
  try {
    const headers = {
      "Content-Type": "application/json",
    };

    if (token) {
      headers["Authorization"] = `Bearer ${token}`;
    }

    const options = {
      method,
      headers,
    };

    if (body) {
      options.body = JSON.stringify(body);
    }

    const response = await fetch(url, options);    
    const code = response.status
    if (code == 401) {
      toast.error(`Akun belum login, silakan login kembali`)
      setTimeout(() => {
        window.location.href = '/#/login'
      },1500)
      throw new Error(data.messages || `Error, code: ${code}`);      
    }
    if (code == 500) {
      toast.error(`Terjadi kesalahan, code: ${code}`)
      throw new Error(data.messages || `Error, code: ${code}`);
    }

    const data = await response.json();
    if (data.status == 'fail') {
      toast.error(data.messages)
      throw new Error(data.messages || "Terjadi kesalahan pada API");
    }else if(data.status == 'ok'){
      toast.success(data.messages);
    }
    return data;
  } catch (error) {
    throw new Error(error.message);
  }
};
