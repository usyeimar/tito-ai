import stancl from './stancl'
import profilePicture from './profile-picture'
import entityProfilePicture from './entity-profile-picture'
import entityFavicon from './entity-favicon'

const tenant = {
    stancl: Object.assign(stancl, stancl),
    profilePicture: Object.assign(profilePicture, profilePicture),
    entityProfilePicture: Object.assign(entityProfilePicture, entityProfilePicture),
    entityFavicon: Object.assign(entityFavicon, entityFavicon),
}

export default tenant