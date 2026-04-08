import Auth from './Auth'
import Tenancy from './Tenancy'

const API = {
    Auth: Object.assign(Auth, Auth),
    Tenancy: Object.assign(Tenancy, Tenancy),
}

export default API