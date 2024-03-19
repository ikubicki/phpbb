db = new Mongo().getDB("phpbb-auth")

// users

db.createCollection('users')
db.users.insertMany([
    {
        uuid: '255612c9-aa2c-4dec-b4c3-9f59831fd5c6',
        name: 'Standard user'
    },
    {
        name: 'Moderator',
        uuid: 'a3d47af6-808e-4f9e-9d04-d6afb6120034'
    },
    {
        uuid: 'cfe2134e-1e69-47c5-b12d-05d47b94ff0c',
        name: 'Administrator',
        status: 'active',
        metadata: {
            avatar: {
                url: 'https://codebuilders.pl/avatar.jpg'
            }
        }
    }
])
db.users.createIndex({ uuid: 1 }, { unique: true })
db.users.createIndex({ name: 1 }, { unique: true })

// organisations

db.createCollection('organisations')
db.organisations.insertMany([
    {
        uuid: 'b6f5fd65-1510-4a98-bd14-61740cb834f8',
        type: 'group',
        name: 'Threads moderators',
        description: 'Group of moderators',
        creator: 'cfe2134e-1e69-47c5-b12d-05d47b94ff0c',
        created: 1800000000,
        modified: null
    },
    {
        uuid: 'bd80d180-e424-425d-9ed9-75a8236a8a8d',
        type: 'group',
        name: 'System administrators',
        description: 'Group of administrators',
        creator: 'cfe2134e-1e69-47c5-b12d-05d47b94ff0c',
        created: 1800000000,
        modified: null
    },
    {
        uuid: '17580941-d068-4004-9468-e959778d2b7e',
        type: 'team',
        name: 'Staff',
        description: 'A group of people',
        creator: 'cfe2134e-1e69-47c5-b12d-05d47b94ff0c',
        created: 1800000000,
        modified: null
    }
])
db.organisations.createIndex({ uuid: 1, name: 1 }, { unique: true })

// authentications

db.createCollection('authentications')
db.authentications.insertMany([
    {
        "type": "password",
        "identifier": "admin",
        "kid": "b0208cdc-3750-4b1a-a552-0665ab7d8c90",
        "owner": "cfe2134e-1e69-47c5-b12d-05d47b94ff0c",
        "signature": "v1.acffae7125ad094e11e146690e7b6aae644355b75c40dbe25dd56c5731fa0436"
    }
])
db.organisations.createIndex({ type: 1, id: 1 }, { unique: true })