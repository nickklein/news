import Checkbox from '@/Components/Checkbox';
import axios from 'axios';
import { useState } from 'react';

function NewsSources(props) {
    const [sources, setSources] = useState(props.sources);

    /**
     * Update source state, and update back end
     * @param event 
     */
    function onHandleChange(event) {
        setSources(oldSources => {
            return oldSources.map((item) => {
                if (item.source_id == event.target.value) {
                    return {
                        ...item,
                        active: !item.active
                    }
                }
                return item;
            });
        })
        axios.post(props.sourceUpdateUrl, {'sourceId': parseInt(event.target.value), 'state': event.target.checked})
    }

    return (
        <>
            <header>
                <h2 className="text-lg font-medium text-gray-900 dark:text-gray-100">News Sources</h2>
                <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                These are all the news sources it currently supports
                </p>
            </header>

            {sources && sources.map((source, index) => {
                return (
                    <div className="block mt-4" key={index}>
                        <label className="flex items-center">
                            <Checkbox name={source.source_name} value={source.source_id} checked={source.active} handleChange={onHandleChange} />
                            <span className="ml-2 text-sm text-gray-600 dark:text-gray-400">{source.source_name}</span>
                        </label>
                    </div>
                )
            })}
            
        </>
    )
}

export default NewsSources