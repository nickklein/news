import React from 'react'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import NewsSources from './Partials/NewsSources';
import Tags from '../../Partials/Tags';

function Edit(props) {
    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manage News</h2>}
        >
            <Head title="Manage News" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <section>
                                <NewsSources 
                                    sources={props.sources}
                                    sourceUpdateUrl={props.sourceUpdateUrl}
                                />                         
                        </section>
                    </div>

                    <div className="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                        <section>
                                <Tags
                                    selected={props.tags.data}
                                    tagsAddUrl={props.tagsAddUrl}
                                    tagsRemoveUrl={props.tagsRemoveUrl}
                                    description="These are all the tags used to fetch the news for you"
                                />                 
                        </section>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    )
}

export default Edit
