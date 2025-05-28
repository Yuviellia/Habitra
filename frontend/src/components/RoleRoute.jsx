import React from 'react';
import { Navigate } from 'react-router-dom';
import { getUserRoles } from '../auth';

const RoleRoute = ({ allowedRoles, children }) => {
    const token = localStorage.getItem('token');
    if (!token) {
        return <Navigate to="/login" replace />;
    }

    const roles = getUserRoles();
    const hasAllowed = roles.some(role => allowedRoles.includes(role));
    if (hasAllowed) {
        return children;
    }

    if (roles.includes('ROLE_ADMIN')) {
        return <Navigate to="/users" replace />;
    }
    if (roles.includes('ROLE_USER')) {
        return <Navigate to="/habits" replace />;
    }

    return <Navigate to="/login" replace />;
};

export default RoleRoute;
