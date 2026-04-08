import Web from './Web'
import API from './API'

const Tenant = {
    Web: Object.assign(Web, Web),
    API: Object.assign(API, API),
}

export default Tenant