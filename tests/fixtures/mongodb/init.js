db = new Mongo().getDB("phpbb-auth")

// collections

db.createCollection('authentications')
db.createCollection('memberships')
db.createCollection('organisations')
db.createCollection('policies')
db.createCollection('users')

// indexes

db.authentications.createIndex({ type: 1, id: 1 }, { unique: true })
db.memberships.createIndex({ member: 1 }, { unique: true })
db.organisations.createIndex({ uuid: 1, name: 1 }, { unique: true })
db.policies.createIndex({ principal: 1 })
db.users.createIndex({ uuid: 1 }, { unique: true })
db.users.createIndex({ name: 1 }, { unique: true })

// users

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

// organisations

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
    },
    {
        uuid: '6091db6f-8d63-417a-9605-b39eb264efc4',
        type: 'group',
        name: 'Everyone',
        description: 'All members',
        'default': true,
        creator: 'cfe2134e-1e69-47c5-b12d-05d47b94ff0c',
        created: 1800000000,
        modified: null
    }
])

// authentications

db.authentications.insertMany([
    {
        "type": "password",
        "identifier": "admin",
        "kid": "b0208cdc-3750-4b1a-a552-0665ab7d8c90",
        "owner": "cfe2134e-1e69-47c5-b12d-05d47b94ff0c",
        "signature": "v1.acffae7125ad094e11e146690e7b6aae644355b75c40dbe25dd56c5731fa0436"
    }
])

// memberships

db.memberships.insertMany([
    {
        "member": 'cfe2134e-1e69-47c5-b12d-05d47b94ff0c',
        "organisations": [
            'b6f5fd65-1510-4a98-bd14-61740cb834f8',
            'bd80d180-e424-425d-9ed9-75a8236a8a8d',
            '17580941-d068-4004-9468-e959778d2b7e',
            '6091db6f-8d63-417a-9605-b39eb264efc4'
        ]
    },
    {
        "member": 'a3d47af6-808e-4f9e-9d04-d6afb6120034',
        "organisations": [
            'b6f5fd65-1510-4a98-bd14-61740cb834f8',
            '17580941-d068-4004-9468-e959778d2b7e',
            '6091db6f-8d63-417a-9605-b39eb264efc4'
        ]
    }
])

// policies

db.policies.insertMany([
    {
        principal: 'b6f5fd65-1510-4a98-bd14-61740cb834f8',
        policies: [
            {
                resource: '*',
                access: 'threads.moderate'
            }
        ]
    },
    {
        "principal": '6091db6f-8d63-417a-9605-b39eb264efc4',
        "policies": [
            {
                "resource": '*',
                "access": 'users.view'
            },
            {
                "resource": '*',
                "access": 'organisations.view'
            },
            {
                resources: [
                    'categories:0a704330-e00c-4e48-ba95-be2537adb261',
                    'categories:40d5f22b-daa7-4ab9-be39-415dc11c372d'
                ],
                access: [
                    'categories.view',
                    'categories.post'
                ]
            }
        ]
    },
    {
        principal: 'bd80d180-e424-425d-9ed9-75a8236a8a8d',
        policies: [
            {
                resource: '*',
                access: '*'
            }
        ]
    },
    {
        principal: 'cfe2134e-1e69-47c5-b12d-05d47b94ff0c',
        policies: [
            {
                resource: '*',
                access: '*'
            }
        ]
    }
])

