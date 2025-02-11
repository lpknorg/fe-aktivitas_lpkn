import React from 'react'
import {
  CAvatar,
  CBadge,
  CDropdown,
  CDropdownDivider,
  CDropdownHeader,
  CDropdownItem,
  CDropdownMenu,
  CDropdownToggle,
} from '@coreui/react'
import {
  cilBell,
  cilCreditCard,
  cilCommentSquare,
  cilEnvelopeOpen,
  cilFile,
  cilLockLocked,
  cilSettings,
  cilTask,
  cilUser,
} from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import CryptoJS from "crypto-js";

import avatar8 from './../../assets/images/avatars/8.jpg'
import { apiRequest } from "../../../utils/api"; // Import helper API
import { useNavigate } from "react-router-dom";
import { ToastContainer, toast } from 'react-toastify';

const AppHeaderDropdown = () => {
  const navigate = useNavigate();

  const handleLogout = async (tokens) => {
    const encryptedToken = localStorage.getItem("token_");
    if (!encryptedToken) return null;
    const encToken = CryptoJS.AES.decrypt(encryptedToken, "SECRET_KEY").toString(CryptoJS.enc.Utf8);
    console.log(encToken)

    try {
      const data = await apiRequest("http://localhost:8000/api/logout", "POST", null, encToken);
      toast.success(data.messages);
      setTimeout(() => {
        navigate('/login')
      }, 1500);
    } catch (error) {
      toast.error(error.messages);
    }
  };
  return (
    <CDropdown variant="nav-item">
    <ToastContainer />
      <CDropdownToggle placement="bottom-end" className="py-0 pe-0" caret={false}>
        <CAvatar src={avatar8} size="md" />
      </CDropdownToggle>
      <CDropdownMenu className="pt-0" placement="bottom-end">
        <CDropdownHeader className="bg-body-secondary fw-semibold mb-2">Account</CDropdownHeader>
        <CDropdownItem href="#" onClick={(e) => { e.preventDefault(); handleLogout(); }}>
          <CIcon icon={cilLockLocked} className="me-2" />
          Logout
        </CDropdownItem>
      </CDropdownMenu>
    </CDropdown>
  )
}

export default AppHeaderDropdown
