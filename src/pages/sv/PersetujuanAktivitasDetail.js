import React, { useState, useEffect } from "react";
import { CButton, CCard, CCardBody, CCardHeader, CCol, CRow, CForm, CFormInput, CFormLabel, CFormTextarea, CFormSelect } from "@coreui/react";
import { useNavigate, useParams } from "react-router-dom";
import { toast } from "react-toastify";
import { apiRequest } from "../../utils/api"; // Import helper API
import { getTokens } from "../../utils/userStorage";

const AktivitasAdd = () => {
  const { idPersetujuan } = useParams();
  const [aktivitas, setAktivitas] = useState([]);
  const [status, setStatus] = useState("");
  const [komentar, setKomentar] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const detailAktivitas = async () => {
      try {
        const token = getTokens();
        const data = await apiRequest(`aktivitas-kerja/${idPersetujuan}`, "GET", null, token);
        setAktivitas(data);
      } catch (error) {
        toast.error(error.messages);
      } finally {
        // setLoading(false);
      }
    };

    detailAktivitas();
  }, []);

  const handlePersetujuanAktivitas = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const token = getTokens();
      const data = await apiRequest(
        `persetujuan/${idPersetujuan}`,
        "POST",
        {
          status,
          komentar,
        },
        token
      );
      if (data.status === "ok") {
        setTimeout(() => {
          navigate("/persetujuan_aktivitas");
        }, 1500);
      }
    } catch (error) {
      toast.error(error.messages);
    } finally {
      setLoading(false);
    }
  };

  return (
    <CRow>
      <CCol xs={12}>
        <CCard className="mt-2">
          <CCardHeader>
            <strong>Detail Aktivitas</strong>
          </CCardHeader>
          <CCardBody>
            <CForm onSubmit={handlePersetujuanAktivitas}>
              <div className="mb-3">
                <CFormLabel htmlFor="exampleFormControlInput1">Judul</CFormLabel>
                <CFormInput type="text" id="exampleFormControlInput1" placeholder="Masukkan judul" disabled value={aktivitas.judul} />
              </div>
              <div className="mb-3">
                <CFormLabel htmlFor="exampleFormControlTextarea1">Deksripsi</CFormLabel>
                <CFormTextarea disabled id="exampleFormControlTextarea1" rows={3} placeholder="Masukkan deskripsi ..." value={aktivitas.deskripsi}></CFormTextarea>
              </div>
              <CCol className="row g-3">
                <CCol md={4}>
                  <div className="mb-3">
                    <CFormLabel htmlFor="exampleFormControlInput1">Kategori</CFormLabel>
                    <CFormSelect disabled aria-label="Default select example">
                      <option>Pilih kategori</option>
                      <option value="harian" selected={aktivitas.kategori === "harian"}>
                        Harian
                      </option>
                      <option value="mingguan" selected={aktivitas.kategori === "mingguan"}>
                        Mingguan
                      </option>
                      <option value="proyek" selected={aktivitas.kategori === "proyek"}>
                        Proyek
                      </option>
                      <option value="lembur" selected={aktivitas.kategori === "lembur"}>
                        Lembur
                      </option>
                    </CFormSelect>
                  </div>
                </CCol>
                <CCol md={4}>
                  <div className="mb-3">
                    <CFormLabel htmlFor="exampleFormControlInput1">Waktu Mulai</CFormLabel>
                    <CFormInput type="datetime-local" id="exampleFormControlInput1" placeholder="Masukkan waktu mulai" disabled value={aktivitas.waktu_mulai} />
                  </div>
                </CCol>
                <CCol md={4}>
                  <div className="mb-3">
                    <CFormLabel htmlFor="exampleFormControlInput1">Waktu Selesai</CFormLabel>
                    <CFormInput type="datetime-local" id="exampleFormControlInput1" placeholder="Masukkan waktu Selesai" disabled value={aktivitas.waktu_selesai} />
                  </div>
                </CCol>
                <CCol md={6}>
                  <div className="mb-3">
                    <CFormLabel htmlFor="exampleFormControlInput1">Pilih Status</CFormLabel>
                    <CFormSelect aria-label="Default select example" onChange={(e) => setStatus(e.target.value)} disabled={aktivitas.status != "pengajuan_ulang" && aktivitas.status != "pending"}>
                      <option>Pilih Status</option>
                      <option value="pending" selected={aktivitas.status == "pending"}>
                        Pending
                      </option>
                      <option value="diterima" selected={aktivitas.status == "diterima"}>
                        Diterima
                      </option>
                      <option value="revisi" selected={aktivitas.status == "revisi"}>
                        Revisi
                      </option>
                    </CFormSelect>
                  </div>
                </CCol>
                <CCol md={6}>
                  <div className="mb-3">
                    <CFormLabel htmlFor="exampleFormControlInput1">Komentar</CFormLabel>
                    <CFormTextarea
                      id="exampleFormControlTextarea1"
                      onChange={(e) => setKomentar(e.target.value)}
                      rows={3}
                      placeholder="Masukkan komentar ..."
                      disabled={aktivitas.status != "pengajuan_ulang" && aktivitas.status != "pending"}
                      value={aktivitas.persetujuan_terakhir?.komentar}
                    ></CFormTextarea>
                  </div>
                </CCol>
                <CButton color="primary" type="submit" disabled={loading || (aktivitas.status != "pengajuan_ulang" && aktivitas.status != "pending")}>
                  {loading ? "Loading..." : "Submit"}
                </CButton>
              </CCol>
            </CForm>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  );
};

export default AktivitasAdd;
