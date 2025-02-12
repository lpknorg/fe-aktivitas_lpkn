import React, {useState} from 'react'
import classNames from 'classnames'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CRow,
  CForm,
  CFormInput,
  CFormLabel,
  CFormTextarea,
  CFormSelect
} from '@coreui/react'
import { DocsComponents, DocsExample } from '../template/components'
import { useNavigate } from "react-router-dom";
import { toast } from 'react-toastify';
import { apiRequest } from "../utils/api"; // Import helper API
import { getUser, getTokens} from "../utils/userStorage";

const AktivitasAdd = () => {
  const [judul, setJudul] = useState("");
  const [deskripsi, setDeskripsi] = useState("");
  const [kategori, setKategori] = useState("");
  const [waktu_mulai, setWaktuMulai] = useState("");
  const [waktu_selesai, setWaktuSelesai] = useState("");
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleSubmitAktivitas = async (e) => {
    e.preventDefault();
    setLoading(true);

    try {
      const token  = getTokens()
      const data = await apiRequest("http://localhost:8000/api/aktivitas-kerja", "POST", {
        judul,
        deskripsi,
        kategori,
        waktu_mulai,
        waktu_selesai,
        kategori
      }, token);
      if (data.status == 'ok') {
        setTimeout(() => {
          navigate('/aktivitas')
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
            <strong>Add Aktivitas</strong>
          </CCardHeader>
          <CCardBody>
            <CForm onSubmit={handleSubmitAktivitas}>
                <div className="mb-3">
                  <CFormLabel htmlFor="exampleFormControlInput1">Judul</CFormLabel>
                  <CFormInput
                    type="text"
                    id="exampleFormControlInput1"
                    placeholder="Masukkan judul"
                    onChange={(e) => setJudul(e.target.value)}
                  />
                </div>
                <div className="mb-3">
                  <CFormLabel htmlFor="exampleFormControlTextarea1">Deksripsi</CFormLabel>
                  <CFormTextarea id="exampleFormControlTextarea1" rows={3} placeholder="Masukkan deskripsi ..." onChange={(e) => setDeskripsi(e.target.value)}></CFormTextarea>
                </div>                
                <CCol className="row g-3">
                  <CCol md={4}>
                    <div className="mb-3">
                      <CFormLabel htmlFor="exampleFormControlInput1">Kategori</CFormLabel>
                      <CFormSelect aria-label="Default select example" onChange={(e) => setKategori(e.target.value)}>
                        <option>Pilih kategori</option>
                        <option value="harian">Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="proyek">Proyek</option>
                        <option value="lembur">Lembur</option>
                      </CFormSelect>
                    </div>
                  </CCol>
                  <CCol md={4}>
                    <div className="mb-3">
                      <CFormLabel htmlFor="exampleFormControlInput1">Waktu Mulai</CFormLabel>
                      <CFormInput
                        type="datetime-local"
                        id="exampleFormControlInput1"
                        placeholder="Masukkan waktu mulai"
                        onChange={(e) => setWaktuMulai(e.target.value)}
                      />
                    </div>
                    </CCol>
                    <CCol md={4}>
                    <div className="mb-3">
                      <CFormLabel htmlFor="exampleFormControlInput1">Waktu Selesai</CFormLabel>
                      <CFormInput
                        type="datetime-local"
                        id="exampleFormControlInput1"
                        placeholder="Masukkan waktu Selesai"
                        onChange={(e) => setWaktuSelesai(e.target.value)}
                      />
                    </div>
                  </CCol>
                  <CButton color="primary" type="submit" disabled={loading}>{loading ? "Loading..." : "Submit"}</CButton>
                </CCol>
              </CForm>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  )
}

export default AktivitasAdd
