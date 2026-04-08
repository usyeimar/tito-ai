import PasskeyLoginController from './PasskeyLoginController'
import PasskeyRegistrationController from './PasskeyRegistrationController'
import PasskeyController from './PasskeyController'

const Passkey = {
    PasskeyLoginController: Object.assign(PasskeyLoginController, PasskeyLoginController),
    PasskeyRegistrationController: Object.assign(PasskeyRegistrationController, PasskeyRegistrationController),
    PasskeyController: Object.assign(PasskeyController, PasskeyController),
}

export default Passkey