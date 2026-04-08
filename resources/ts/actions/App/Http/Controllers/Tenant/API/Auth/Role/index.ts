import PermissionController from './PermissionController'
import RoleController from './RoleController'

const Role = {
    PermissionController: Object.assign(PermissionController, PermissionController),
    RoleController: Object.assign(RoleController, RoleController),
}

export default Role