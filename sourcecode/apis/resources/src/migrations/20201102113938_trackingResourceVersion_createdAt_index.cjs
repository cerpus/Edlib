exports.up = function (knex) {
    return knex.schema.table('trackingResourceVersion', function (table) {
        table.index('createdAt');
    });
};

exports.down = function (knex) {
    return knex.schema.table('trackingResourceVersion', function (table) {
        table.dropIndex('createdAt');
    });
};
