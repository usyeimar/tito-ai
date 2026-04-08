import Authentication from './Authentication'
import Tfa from './Tfa'
import Password from './Password'
import SocialLogin from './SocialLogin'
import Passkey from './Passkey'
import Invitation from './Invitation'
import Profile from './Profile'
import Impersonation from './Impersonation'
import Token from './Token'
import EmailVerification from './EmailVerification'

const Auth = {
    Authentication: Object.assign(Authentication, Authentication),
    Tfa: Object.assign(Tfa, Tfa),
    Password: Object.assign(Password, Password),
    SocialLogin: Object.assign(SocialLogin, SocialLogin),
    Passkey: Object.assign(Passkey, Passkey),
    Invitation: Object.assign(Invitation, Invitation),
    Profile: Object.assign(Profile, Profile),
    Impersonation: Object.assign(Impersonation, Impersonation),
    Token: Object.assign(Token, Token),
    EmailVerification: Object.assign(EmailVerification, EmailVerification),
}

export default Auth