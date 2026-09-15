define(['table'], function (Table) {
    return {
        api: {
            getCustomFields: function (fields, table) {
                var result = [];
                $.each(fields, function (i, j) {
                    if (j.type === 'editor') {
                        return true;
                    }
                    var param = {field: j.field, title: j.title, table: table, operate: (j.type === 'number' ? '=' : 'like'), formatter: Table.api.formatter.content, class: 'autocontent'};
                    //如果是图片,加上formatter
                    if (j.type === 'image' || j.type === 'images') {
                        param.events = Table.api.events.image;
                        param.formatter = Table.api.formatter.images;
                    } else if (j.type === 'file' || j.type === 'files') {
                        param.formatter = Table.api.formatter.files;
                    } else if (j.type === 'radio' || j.type === 'checkbox' || j.type === 'select' || j.type === 'selects') {
                        param.formatter = Table.api.formatter.label;
                        param.extend = j.content;
                        param.searchList = j.content;
                    } else {
                        param.formatter = Table.api.formatter.content;
                        param.classname = 'autocontent';
                    }
                    result.push(param);
                });
                return result;
            }
        }
    }
});