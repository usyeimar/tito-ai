import Central from './Central'
import Shared from './Shared'
import Tenant from './Tenant'

const Controllers = {
    Central: Object.assign(Central, Central),
    Shared: Object.assign(Shared, Shared),
    Tenant: Object.assign(Tenant, Tenant),
}

export default Controllers