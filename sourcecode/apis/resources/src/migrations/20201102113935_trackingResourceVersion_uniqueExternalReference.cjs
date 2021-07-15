exports.up = function (knex) {
    return knex.schema.table('trackingResourceVersion', function (table) {
        table.unique('externalReference');
    });
};

exports.down = function (knex) {
    return knex.schema.table('trackingResourceVersion', function (table) {
        table.dropUnique('externalReference');
    });
};
