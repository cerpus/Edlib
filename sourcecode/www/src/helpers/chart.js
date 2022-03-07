import moment from 'moment';

export const fillEmptyDays = (data, from, to, { zeroFields }) => {
    const date = from.clone().startOf('day');
    const actualTo = to.clone().endOf('day');

    const newData = [];

    while (date.isBefore(actualTo)) {
        const stored = data.find((d) => moment(d.date).isSame(date, 'day'));

        newData.push(
            zeroFields.reduce(
                (result, field) => {
                    if (result[field] == null) {
                        result[field] = 0;
                    }
                    return result;
                },
                {
                    ...stored,
                    date: date.format('DD. MMM.'),
                }
            )
        );
        date.add(1, 'day');
    }

    return newData;
};

export const merge = (datasets) => {
    const byDate = datasets.reduce(
        (final, { key, dataset }) =>
            dataset.reduce((final, entry) => {
                if (!final[entry.date]) {
                    final[entry.date] = {
                        date: entry.date,
                    };
                }
                final[entry.date][key] = entry.value;
                return final;
            }, final),
        {}
    );

    return Object.values(byDate);
};
