// src/utils/auth.js

function parseJwt(token) {
    try {
        const payload = token.split('.')[1];
        const decoded = atob(payload.replace(/-/g, '+').replace(/_/g, '/'));
        return JSON.parse(decoded);
    } catch {
        return null;
    }
}

export function getUserRoles() {
    const token = localStorage.getItem('token');
    if (!token) return [];
    const payload = parseJwt(token);
    if (!payload || !payload.roles) return [];
    // payload.roles may be a single string or an array
    return Array.isArray(payload.roles) ? payload.roles : [payload.roles];
}

export function isUser() {
    return getUserRoles().includes('ROLE_USER');
}

export function isAdmin() {
    return getUserRoles().includes('ROLE_ADMIN');
}
