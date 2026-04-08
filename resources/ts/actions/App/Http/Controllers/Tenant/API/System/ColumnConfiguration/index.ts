import SystemColumnConfigurationsController from './SystemColumnConfigurationsController'
import SystemUserColumnConfigurationsController from './SystemUserColumnConfigurationsController'

const ColumnConfiguration = {
    SystemColumnConfigurationsController: Object.assign(SystemColumnConfigurationsController, SystemColumnConfigurationsController),
    SystemUserColumnConfigurationsController: Object.assign(SystemUserColumnConfigurationsController, SystemUserColumnConfigurationsController),
}

export default ColumnConfiguration