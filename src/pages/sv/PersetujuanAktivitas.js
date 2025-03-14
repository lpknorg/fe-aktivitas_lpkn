import React, { useEffect, useState } from "react";
import { CButton, CBadge, CCard, CCardBody, CCardHeader, CCol, CRow, CTable, CTableBody, CTableDataCell, CTableHead, CTableHeaderCell, CTableRow } from "@coreui/react";
import { useNavigate } from "react-router-dom";
import { apiRequest } from "../../utils/api"; // Import helper API
import { getTokens } from "../../utils/userStorage";
import { toast } from "react-toastify";

const PersetujuanAktivitas = () => {
  const navigate = useNavigate();
  const [aktivitas, setAktivitas] = useState([]);

  const handleDetail = (id) => {
    navigate(`/persetujuan_aktivitas/${id}/detail`);
  };

  useEffect(() => {
    const fetchDataAktivitas = async () => {
      try {
        const token = getTokens();
        const data = await apiRequest("aktivitas-kerja", "GET", null, token);
        setAktivitas(data);
      } catch (error) {
        toast.error(error.messages);
      } finally {
        // setLoading(false);
      }
    };

    fetchDataAktivitas();
  }, []);
  console.log(aktivitas);

  const getStatusView = (status) => {
    if (status == "pending") {
      return <CBadge color="warning">{status}</CBadge>;
    } else if (status == "diterima") {
      return <CBadge color="success">{status}</CBadge>;
    } else {
      return <CBadge color="info">{status}</CBadge>;
    }
  };

  return (
    <CRow>
      <CCol xs={12}>
        <CCard className="mt-2">
          <CCardHeader>
            <strong>Persetujuan Aktivitas</strong>
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
                  <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Aksi</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                {aktivitas.map((akt, index) => (
                  <CTableRow key={index}>
                    <CTableHeaderCell scope="row">{index + 1}</CTableHeaderCell>
                    <CTableDataCell>{akt.judul}</CTableDataCell>
                    <CTableDataCell>{akt.deskripsi}</CTableDataCell>
                    <CTableDataCell>{akt.waktu_mulai}</CTableDataCell>
                    <CTableDataCell>{akt.waktu_selesai}</CTableDataCell>
                    <CTableDataCell>{akt.waktu_selesai}</CTableDataCell>
                    <CTableDataCell>{getStatusView(akt.status)}</CTableDataCell>
                    <CTableDataCell>
                      <CButton color="primary" onClick={() => handleDetail(akt.id)} size="sm">
                        Detail
                      </CButton>
                    </CTableDataCell>
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

export default PersetujuanAktivitas;
