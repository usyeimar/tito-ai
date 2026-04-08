import Auth from './Auth'
import Public from './Public'
import Commons from './Commons'
import Notifications from './Notifications'
import Activity from './Activity'
import System from './System'
import Agent from './Agent'

const API = {
    Auth: Object.assign(Auth, Auth),
    Public: Object.assign(Public, Public),
    Commons: Object.assign(Commons, Commons),
    Notifications: Object.assign(Notifications, Notifications),
    Activity: Object.assign(Activity, Activity),
    System: Object.assign(System, System),
    Agent: Object.assign(Agent, Agent),
}

export default API