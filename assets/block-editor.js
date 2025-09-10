(function(wp){
    if(!wp || !wp.blocks) return;

    const el = wp.element.createElement;
    const Fragment = wp.element.Fragment;
    const InspectorControls = wp.blockEditor?.InspectorControls || wp.editor?.InspectorControls;
    const ServerSideRender = wp.serverSideRender;
    const TextControl = wp.components.TextControl;
    const RangeControl = wp.components.RangeControl;
    const SelectControl = wp.components.SelectControl;
    const registerBlockType = wp.blocks.registerBlockType;

    registerBlockType('vi/vacancies-list', {
        title: 'Vacancies List',
        category: 'widgets',
        attributes: {
            perPage: { type:'number', default:10 },
            sort: { type:'string', default:'published_at' },
            location: { type:'string', default:'' },
            salaryMin: { type:'number', default:0 },
            salaryMax: { type:'number', default:0 },
            paged: { type:'number', default:1 },
            cf7_shortcode: { type:'string', default:'' } 
        },
        edit: function(props){
            const attrs = props.attributes;
            const setAttrs = props.setAttributes;

            return el(Fragment, null,
                el(InspectorControls, null,
                    el(TextControl, {
                        label: 'Location',
                        value: attrs.location,
                        onChange: v => setAttrs({ location: v, paged: 1 })
                    }),
                    el(RangeControl, {
                        label: 'Min Salary',
                        value: attrs.salaryMin,
                        min: 0,
                        max: 100000,
                        onChange: v => setAttrs({ salaryMin: v, paged: 1 })
                    }),
                    el(RangeControl, {
                        label: 'Max Salary',
                        value: attrs.salaryMax,
                        min: 0,
                        max: 100000,
                        onChange: v => setAttrs({ salaryMax: v, paged: 1 })
                    }),
                    el(SelectControl, {
                        label: 'Sort by',
                        value: attrs.sort,
                        options: [
                            { label: 'Date', value: 'published_at' },
                            { label: 'Salary', value: 'salary' },
                            { label: 'Title', value: 'title' }
                        ],
                        onChange: v => setAttrs({ sort: v, paged: 1 })
                    }),
                    el(TextControl, {
                        type: 'number',
                        label: 'Per page',
                        value: attrs.perPage,
                        onChange: v => setAttrs({ perPage: parseInt(v) || 10, paged: 1 })
                    }),
                    el(TextControl, {
                        label: 'Contact Form 7 Shortcode',
                        value: attrs.cf7_shortcode,
                        onChange: v => setAttrs({ cf7_shortcode: v })
                    })
                ),
                el(ServerSideRender, { block: 'vi/vacancies-list', attributes: attrs })
            );
        },
        save: function(){ 
            return null; 
        }
    });

})(window.wp);
