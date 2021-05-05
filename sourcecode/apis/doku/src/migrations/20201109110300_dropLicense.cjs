exports.up = function (knex) {
    return knex.schema.table('dokus', function (table) {
        table.dropColumn('license');
    });
};

exports.down = async (knex) => {};
