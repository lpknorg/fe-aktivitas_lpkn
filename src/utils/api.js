import { toast } from "react-toastify";
import { API_BASE_URL } from "../config";
// src/utils/api.js
export const apiRequest = async (params, method = "GET", body = null, token = null) => {
  const controller = new AbortController(); // Untuk timeout
  const timeout = setTimeout(() => controller.abort(), 60000); // Timeout 60 detik

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
      signal: controller.signal, // Menggunakan AbortController
    };

    if (body) {
      options.body = JSON.stringify(body);
    }

    const url = `${API_BASE_URL}/${params}`;
    const response = await fetch(url, options);

    clearTimeout(timeout); // Hentikan timeout jika request berhasil

    if (!response) {
      throw new Error("Tidak dapat terhubung ke server. Periksa koneksi Anda.");
    }

    const code = response.status;

    if (code === 401) {
      toast.error("Akun belum login, silakan login kembali.");
      setTimeout(() => {
        window.location.href = "/#/login";
      }, 1500);
      throw new Error("Unauthorized (401)");
    }

    if (code === 500 || code == 405) {
      toast.error(`Terjadi kesalahan pada server, kode: ${code}`);
      throw new Error("Internal Server Error (500)");
    }

    let data;
    try {
      data = await response.json();
    } catch (jsonError) {
      throw new Error("Gagal memproses respons dari server.");
    }

    if (data?.status === "fail" || code == "422") {
      toast.error(data.messages || "Terjadi kesalahan pada API.");
      throw new Error(data.messages || "API Error");
    }

    if (data?.status === "ok") {
      toast.success(data.messages);
    }

    return data;
  } catch (error) {
    clearTimeout(timeout); // Pastikan timeout dibersihkan jika error

    if (error.name === "AbortError") {
      toast.error("Request timeout! Server tidak merespons dalam 60 detik.");
      throw new Error("Request timeout (60 detik)");
    }

    if (error.message.includes("Failed to fetch") || error.message.includes("NetworkError")) {
      toast.error("Tidak dapat terhubung ke server. Periksa koneksi internet Anda.");
    }

    throw new Error(error.message);
  }
};
