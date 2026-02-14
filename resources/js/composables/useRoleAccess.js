import { computed } from 'vue';
import { authAPI } from '../services/api';

// Role definitions
export const ROLES = {
  ADMIN: 1,
  OPERATOR: 2,
  KEPSEK: 3,
};

// Role labels
export const ROLE_LABELS = {
  [ROLES.ADMIN]: 'Admin',
  [ROLES.OPERATOR]: 'Operator',
  [ROLES.KEPSEK]: 'Kepala Sekolah',
};

// Menu permissions - define which roles can access which menus
const MENU_PERMISSIONS = {
  'Dashboard': [ROLES.ADMIN, ROLES.OPERATOR, ROLES.KEPSEK],
  'Master Data': [ROLES.ADMIN, ROLES.OPERATOR],
  'Students': [ROLES.ADMIN, ROLES.OPERATOR],
  'Payments': [ROLES.ADMIN, ROLES.OPERATOR, ROLES.KEPSEK],
  'Jurnal': [ROLES.ADMIN, ROLES.OPERATOR],
  'Reports': [ROLES.ADMIN, ROLES.OPERATOR, ROLES.KEPSEK],
  'Diagrams': [ROLES.ADMIN, ROLES.OPERATOR, ROLES.KEPSEK],
  'Users': [ROLES.ADMIN], // Only Admin can access Users menu
  'Backups': [ROLES.ADMIN, ROLES.OPERATOR], // Admin & Operator can access Backups
  'Trash': [ROLES.ADMIN], // Only Admin can access Trash (Updated)
  'Settings': [ROLES.ADMIN], // Only Admin can access Settings (Updated)
  'Sync': [ROLES.ADMIN, ROLES.OPERATOR], // Admin & Operator can access Sync
  'Closing': [ROLES.ADMIN, ROLES.OPERATOR],
};

// Action permissions - define which roles can perform which actions
const ACTION_PERMISSIONS = {
  create: [ROLES.ADMIN, ROLES.OPERATOR], // Kepsek tidak bisa create
  update: [ROLES.ADMIN, ROLES.OPERATOR], // Kepsek tidak bisa update
  delete: [ROLES.ADMIN, ROLES.OPERATOR], // Kepsek tidak bisa delete
  view: [ROLES.ADMIN, ROLES.OPERATOR, ROLES.KEPSEK], // Semua bisa view
};

/**
 * Get current user role
 */
const getCurrentRole = () => {
  const user = authAPI.getCurrentUser();
  return user?.role || null;
};

/**
 * Check if user has access to a menu
 */
const hasMenuAccess = (menuName) => {
  const role = getCurrentRole();
  if (!role) return false;
  
  const allowedRoles = MENU_PERMISSIONS[menuName] || [];
  return allowedRoles.includes(role);
};

/**
 * Check if user can perform an action
 */
const canPerformAction = (action) => {
  const role = getCurrentRole();
  if (!role) return false;
  
  const allowedRoles = ACTION_PERMISSIONS[action] || [];
  return allowedRoles.includes(role);
};

/**
 * Check if user is admin
 */
const isAdmin = () => {
  return getCurrentRole() === ROLES.ADMIN;
};

/**
 * Check if user is operator
 */
const isOperator = () => {
  return getCurrentRole() === ROLES.OPERATOR;
};

/**
 * Check if user is kepsek (read-only)
 */
const isKepsek = () => {
  return getCurrentRole() === ROLES.KEPSEK;
};

/**
 * Check if user can edit (admin or operator)
 */
const canEdit = () => {
  const role = getCurrentRole();
  return role === ROLES.ADMIN || role === ROLES.OPERATOR;
};

/**
 * Check if user can delete (only admin or operator)
 */
const canDelete = () => {
  const role = getCurrentRole();
  return role === ROLES.ADMIN || role === ROLES.OPERATOR;
};

/**
 * Check if user can create (only admin or operator)
 */
const canCreate = () => {
  const role = getCurrentRole();
  return role === ROLES.ADMIN || role === ROLES.OPERATOR;
};

export function useRoleAccess() {
  const currentRole = computed(() => getCurrentRole());
  const isAdminUser = computed(() => isAdmin());
  const isOperatorUser = computed(() => isOperator());
  const isKepsekUser = computed(() => isKepsek());
  const canEditData = computed(() => canEdit());
  const canDeleteData = computed(() => canDelete());
  const canCreateData = computed(() => canCreate());

  return {
    ROLES,
    ROLE_LABELS,
    currentRole,
    isAdminUser,
    isOperatorUser,
    isKepsekUser,
    canEditData,
    canDeleteData,
    canCreateData,
    hasMenuAccess,
    canPerformAction,
    isAdmin,
    isOperator,
    isKepsek,
    canEdit,
    canDelete,
    canCreate,
    getCurrentRole,
  };
}
