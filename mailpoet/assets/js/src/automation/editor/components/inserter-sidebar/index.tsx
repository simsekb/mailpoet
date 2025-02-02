import { Inserter } from '../inserter';

export function InserterSidebar(): JSX.Element {
  return (
    <div className="edit-post-editor__inserter-panel">
      <div className="edit-post-editor__inserter-panel-content">
        <Inserter />
      </div>
    </div>
  );
}
