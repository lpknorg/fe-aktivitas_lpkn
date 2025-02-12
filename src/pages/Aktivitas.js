import React, { useEffect, useState } from 'react'
import classNames from 'classnames'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CRow,
  CTable,
  CTableBody,
  CTableCaption,
  CTableDataCell,
  CTableHead,
  CTableHeaderCell,
  CTableRow,
} from '@coreui/react'
import { DocsComponents, DocsExample } from '../template/components'
import { useNavigate } from "react-router-dom";
import { apiRequest } from "../utils/api"; // Import helper API
import { getTokens} from "../utils/userStorage";
import { toast } from 'react-toastify';

const Aktivitas = () => {
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();
  const [aktivitas, setAktivitas] = useState([]);

  const handleAdd = () => {
    navigate("/aktivitas/add"); // Ganti dengan route tujuan
  };

  useEffect(() => {
    const fetchDataAktivitas = async () => {
      try {
        const token  = getTokens()
        const data = await apiRequest("http://localhost:8000/api/aktivitas-kerja", "GET", null, token);
        setAktivitas(data)        
      } catch (error) {
        toast.error(error.messages);
      } finally {
        setLoading(false);
      }
    };

    fetchDataAktivitas();    
  }, []);
  console.log(aktivitas)


  return (
    <CRow>
      <CCol xs={12}>
      <CButton
      color="primary"
      onClick={handleAdd}
      >Tambah</CButton>
        <CCard className="mt-2">
          <CCardHeader>
            <strong>List Aktivitas</strong>
          </CCardHeader>
          <CCardBody>
            <CTable bordered hover>
                <CTableHead>
                  <CTableRow>
                    <CTableHeaderCell scope="col">#</CTableHeaderCell>
                    <CTableHeaderCell scope="col">Judul</CTableHeaderCell>
                    <CTableHeaderCell scope="col">Deskripsi</CTableHeaderCell>
                    <CTableHeaderCell scope="col">Waktu Mulai</CTableHeaderCell>
                    <CTableHeaderCell scope="col">Waktu Selesai</CTableHeaderCell>
                    <CTableHeaderCell scope="col">Kategori</CTableHeaderCell>
                  </CTableRow>
                </CTableHead>
                <CTableBody>
                {aktivitas.map((akt, index) => (
                  <CTableRow key={index}>
                    <CTableHeaderCell scope="row">{index+1}</CTableHeaderCell>
                    <CTableDataCell>{akt.judul}</CTableDataCell>
                    <CTableDataCell>{akt.deskripsi}</CTableDataCell>
                    <CTableDataCell>{akt.waktu_mulai}</CTableDataCell>
                    <CTableDataCell>{akt.waktu_selesai}</CTableDataCell>
                    <CTableDataCell>{akt.kategori}</CTableDataCell>
                  </CTableRow>
                ))}
                </CTableBody>
              </CTable>
            
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  )
}

export default Aktivitas
