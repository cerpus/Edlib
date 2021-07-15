exports.up = async function (knex) {
    if (knex.client.config.client === 'sqlite3') {
        await knex.schema.table('resourceVersions', function (table) {
            table.dropColumn('ownerId');
        });

        return knex.schema.table('resourceVersions', function (table) {
            table.string('ownerId', 100).nullable();
        });
    }

    return knex.schema.alterTable('resourceVersions', function (table) {
        table.string('ownerId', 100).nullable().alter();
    });
};

exports.down = async function (knex) {};
