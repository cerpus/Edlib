exports.up = function (knex) {
    return knex.schema.table('resourceVersions', function (table) {
        table.string('authorOverwrite', 255).nullable();
    });
};

exports.down = function (knex) {
    return knex.schema.table('resourceVersions', function (table) {
        table.dropColumn('authorOverwrite');
    });
};
