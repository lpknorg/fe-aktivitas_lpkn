import React, { useState, useEffect } from "react";
import { CButton, CCard, CCardBody, CCardHeader, CCol, CRow, CForm, CFormInput, CFormLabel, CFormTextarea, CFormSelect, CTable, CTableHead, CTableRow, CTableHeaderCell, CTableBody, CTableDataCell } from "@coreui/react";
import { useNavigate, useParams } from "react-router-dom";
import { toast } from "react-toastify";
import { apiRequest } from "../utils/api"; // Import helper API
import { getTokens } from "../utils/userStorage";

const AktivitasRevisi = () => {
  const { idAktivitas } = useParams();
  const [judul, setJudul] = useState("");
  const [deskripsi, setDeskripsi] = useState("");
  const [kategori, setKategori] = useState("");
  const [waktu_mulai, setWaktuMulai] = useState("");
  const [waktu_selesai, setWaktuSelesai] = useState("");

  const [aktivitas, setAktivitas] = useState([]);
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  useEffect(() => {
    const detailAktivitas = async () => {
      try {
        const token = getTokens();
        const data = await apiRequest(`aktivitas-kerja/${idAktivitas}`, "GET", null, token);
        setAktivitas(data);
      } catch (error) {
        toast.error(error.messages);
      } finally {
        // setLoading(false);
      }
    };

    detailAktivitas();
  }, []);
  useEffect(() => {
    if (aktivitas) {
      setJudul(aktivitas.judul);
      setKategori(aktivitas?.kategori);
      setDeskripsi(aktivitas?.deskripsi);
      setWaktuMulai(aktivitas?.waktu_mulai);
      setWaktuSelesai(aktivitas?.waktu_selesai);
    }
  }, [aktivitas]);

  const revisiAktivitas = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const token = getTokens();
      const data = await apiRequest(
        `aktivitas-kerja-revisi/${idAktivitas}`,
        "POST",
        {
          judul,
          deskripsi,
          kategori,
          waktu_mulai,
          waktu_selesai,
        },
        token
      );
      if (data.status === "ok") {
        setTimeout(() => {
          navigate("/aktivitas");
        }, 1500);
      }
    } catch (error) {
      toast.error(error.messages);
    } finally {
      setLoading(false);
    }
  };

  return (
    // jika statusnya revisi baru bisa diedit
    <CRow>
      <CCol xs={12}>
        <CCard className="mt-2">
          <CCardHeader>
            <strong>Detail Aktivitas</strong>
          </CCardHeader>
          <CCardBody>
            <CForm onSubmit={revisiAktivitas}>
              <div className="mb-3">
                <CFormLabel htmlFor="exampleFormControlInput1">Judul</CFormLabel>
                <CFormInput type="text" id="exampleFormControlInput1" placeholder="Masukkan judul" value={judul} onChange={(e) => setJudul(e.target.value)} />
              </div>
              <div className="mb-3">
                <CFormLabel htmlFor="exampleFormControlTextarea1">Deksripsi</CFormLabel>
                <CFormTextarea id="exampleFormControlTextarea1" rows={3} placeholder="Masukkan deskripsi ..." value={deskripsi}></CFormTextarea>
              </div>
              <CCol className="row g-3">
                <CCol md={4}>
                  <div className="mb-3">
                    <CFormLabel htmlFor="exampleFormControlInput1">Kategori</CFormLabel>
                    <CFormSelect aria-label="Default select example">
                      <option>Pilih kategori</option>
                      <option value="harian" selected={kategori === "harian"}>
                        Harian
                      </option>
                      <option value="mingguan" selected={kategori === "mingguan"}>
                        Mingguan
                      </option>
                      <option value="proyek" selected={kategori === "proyek"}>
                        Proyek
                      </option>
                      <option value="lembur" selected={kategori === "lembur"}>
                        Lembur
                      </option>
                    </CFormSelect>
                  </div>
                </CCol>
                <CCol md={4}>
                  <div className="mb-3">
                    <CFormLabel htmlFor="exampleFormControlInput1">Waktu Mulai</CFormLabel>
                    <CFormInput type="datetime-local" id="exampleFormControlInput1" placeholder="Masukkan waktu mulai" value={waktu_mulai} />
                  </div>
                </CCol>
                <CCol md={4}>
                  <div className="mb-3">
                    <CFormLabel htmlFor="exampleFormControlInput1">Waktu Selesai</CFormLabel>
                    <CFormInput type="datetime-local" id="exampleFormControlInput1" placeholder="Masukkan waktu Selesai" value={waktu_selesai} />
                  </div>
                </CCol>
                <CButton color="primary" type="submit" disabled={loading}>
                  {loading ? "Loading..." : "Submit"}
                </CButton>
              </CCol>
            </CForm>
          </CCardBody>
        </CCard>
        <CCard className="mt-2">
          <CCardHeader>
            <strong>Riwayat Persetujuan Aktivitas</strong>
          </CCardHeader>
          <CCardBody>
            <CTable bordered hover>
              <CTableHead>
                <CTableRow>
                  <CTableHeaderCell scope="col">#</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Komentar</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Diubah Oleh</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                {aktivitas.list_persetujuan?.map((akt, index) => (
                  <CTableRow key="123">
                    <CTableHeaderCell scope="row">{index + 1}</CTableHeaderCell>
                    <CTableDataCell>{akt.status}</CTableDataCell>
                    <CTableDataCell>{akt.komentar}</CTableDataCell>
                    <CTableDataCell>{akt.reviewer?.name}</CTableDataCell>
                  </CTableRow>
                ))}
              </CTableBody>
            </CTable>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  );
};

export default AktivitasRevisi;
