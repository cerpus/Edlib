exports.up = function (knex) {
    return knex.schema.alterTable('resourceVersions', function (table) {
        table.string('ownerId', 100).nullable().alter();
    });
};

exports.down = function (knex) {
    return knex.schema.table('resourceVersions', function (table) {
        table.string('ownerId', 100).notNullable().alter();
    });
};
