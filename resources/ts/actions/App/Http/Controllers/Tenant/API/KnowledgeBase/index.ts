import KnowledgeBaseController from './KnowledgeBaseController'
import KnowledgeBaseCategoryController from './KnowledgeBaseCategoryController'
import KnowledgeBaseDocumentController from './KnowledgeBaseDocumentController'

const KnowledgeBase = {
    KnowledgeBaseController: Object.assign(KnowledgeBaseController, KnowledgeBaseController),
    KnowledgeBaseCategoryController: Object.assign(KnowledgeBaseCategoryController, KnowledgeBaseCategoryController),
    KnowledgeBaseDocumentController: Object.assign(KnowledgeBaseDocumentController, KnowledgeBaseDocumentController),
}

export default KnowledgeBase